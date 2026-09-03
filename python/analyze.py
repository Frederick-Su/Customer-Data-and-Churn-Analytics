import sys
import os
import pandas as pd
import matplotlib.pyplot as plt
from statsmodels.graphics.mosaicplot import mosaic
import seaborn as sns
import numpy as np
import json
import matplotlib.dates as mdates
from scipy.stats import pearsonr
from sklearn.linear_model import LinearRegression

excel_path = sys.argv[1]
analysis_id = sys.argv[2]

file_ext = os.path.splitext(excel_path)[1].lower()

if file_ext == '.csv':
    df = pd.read_csv(excel_path)
elif file_ext in ['.xlsx', '.xls']:
    df = pd.read_excel(excel_path)
else:
    raise ValueError(f"Unsupported file format '{file_ext}'. Please provide a .xlsx, .xls, or .csv file.")

original_df = df.copy()

# Define the project root and where the images are uploaded to (storage/app/public/results)
project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
output_dir = os.path.join(
    project_root,
    "storage",
    "app",
    "public",
    "results",
    analysis_id
)

os.makedirs(output_dir, exist_ok=True)

# Pre-Processing (ONLY NEEDED IF DATASET IS INCOMPLETE / MESSY)
df = df.replace('CORP JAKARTA', 'JAKARTA')
df = df.replace('CORP BANDUNG', 'BANDUNG')

df = df.replace('TERMINATED', 'Terminated')
df = df.replace('terminated', 'Terminated')
df = df.replace('COMPLIMENT', 'Compliment')
df = df.replace('AKTIF', 'Aktif')
df = df.replace('Griya Artha Sepatan', 'GRIYA ARTHA SEPATAN')
df = df.replace('Asthara', 'ASTHARA')

# Format and find Service Duration
def parse_date(column):
    return pd.to_datetime(column, dayfirst=True, format='mixed')

df['Created'] = parse_date(df['Created'])
df['Renewed'] = parse_date(df['Renewed'])

df['Expired'] = df['Expired'].replace('0000-00-00 00:00:00', np.nan)
df['Expired'] = parse_date(df['Expired'])

# Find the latest date in Created and Renewed (this is to estimate the dataset creation date)
latest_data_date = max(
    df['Created'].max(),
    df['Renewed'].max()
)
# This is done because some columns have dates going up to a year ahead, which messes up the graphs.
eval_date = (
    latest_data_date
).normalize()

# Initialize Duration_days to handle all cases
df['Duration_days'] = np.nan

# Case 1: Status_Customer = 'Aktif'
df.loc[df['Status Customer'] == 'Aktif', 'Duration_days'] = (
    eval_date - df['Created'].dt.normalize()
).dt.days

# Case 2: Status_Customer = 'Terminated' or 'Isolir'
df.loc[
    (df['Status Customer'].isin(['Terminated', 'Isolir'])) & (df['Expired'].notna()),
    'Duration_days'
] = (df['Expired'].dt.normalize() - df['Created'].dt.normalize()).dt.days

# Remove negative outliers (There should be 2 in Jakarta)
df = df[df['Duration_days'] >= 0]

df['InvoiceDate'] = parse_date(df['InvoiceDate'])

# Calculate the number of days between invoice date and expiry date
# Calculate Invoice_to_Expiry
df['Invoice_to_Expiry'] = (
    df['Expired'] - df['InvoiceDate']
).dt.days

# Create a separate DataFrame specifically for Invoice_to_Expiry
invoice_to_expiry_df = df[
    (df['Status Customer'] == 'Aktif') &
    (df['Invoice_to_Expiry'].notna())
].copy()



# Graph 1 (mosaic.png)
plt.rcParams["figure.figsize"] = [10.00, 3.50]
plt.rcParams["figure.autolayout"] = True
mosaic(df, index=['Region', 'Status Customer'], title='Mosaic Plot')
plt.savefig(os.path.join(output_dir, "1_mosaic.png"))
plt.close()

summary_mosaic = (
    df.groupby(["Region", "Status Customer"])
      .size()
      .unstack(fill_value=0)
)

summary_mosaic["Total"] = summary_mosaic.sum(axis=1)
summary_mosaic.loc["Total"] = summary_mosaic.sum()

with open(os.path.join(output_dir, "1_mosaic.json"), "w") as f:
    json.dump(summary_mosaic.astype(int).to_dict(), f, indent=4)

# Graph 2 (tenure.png)
df['Duration_months'] = df['Duration_days'] / 30.44

plt.figure(figsize=(10, 6))

ax = sns.boxplot(
    data=df,
    x='Region',
    y='Duration_months',
    palette='Pastel1'
)

sns.stripplot(
    data=df,
    x='Region',
    y='Duration_months',
    color='black',
    alpha=0.2,
    jitter=True
)

ax.set_xlabel("Region", fontsize=12, fontweight="bold")
ax.set_ylabel("Tenure (Months)", fontsize=12, fontweight="bold")
ax.set_title("Customer Tenure by Region", fontsize=14, pad=15)

plt.xticks(rotation=45)
plt.savefig(os.path.join(output_dir, "2_tenure.png"))
plt.close()

summary = (
    df.groupby("Region")["Duration_months"]
      .describe()
      .round(2)
      .to_dict()
)

with open(os.path.join(output_dir, "2_tenure.json"), "w") as f:
    json.dump(summary, f, indent=4)

# Raw per-customer records for the interactive tenure scatter chart
# (Region + Site filters are applied client-side against this file)
tenure_records = (
    df[['Region', 'Site', 'Duration_months']]
    .dropna(subset=['Region', 'Site', 'Duration_months'])
    .copy()
)
 
tenure_records['Duration_months'] = tenure_records['Duration_months'].round(2)
 
with open(os.path.join(output_dir, "2_tenure_records.json"), "w") as f:
    json.dump(
        tenure_records.to_dict(orient='records'),
        f,
        indent=4
    )

# Graph 3 (site_loyalty.png)
df['Tenure_months'] = df['Duration_days'] / 30

loyalty = (
    df.groupby('Site')['Tenure_months']
      .mean()
      .sort_values(ascending=False)
)

summary = (
    df.groupby('Site')
      .agg(
          Customers=('Tenure_months', 'count'),
          Avg_Tenure=('Tenure_months', 'mean')
      )
      .round(2)
      .sort_values('Avg_Tenure', ascending=False)
      .to_dict()
)

with open(os.path.join(output_dir, "3_site_loyalty.json"), "w") as f:
    json.dump(summary, f, indent=4)

loyalty.plot(kind='barh', figsize=(10, 5))
plt.xlabel("Average Tenure (months)")
plt.title("Average Customer Loyalty by Site")
plt.savefig(os.path.join(output_dir, "3_site_loyalty.png"))
plt.close()


# Graph 4 (Active Customers)
months = pd.period_range(
    start=df['Created'].min().to_period('M'),
    end=eval_date.to_period('M') - 1,
    freq='M'
)

results = []

for region in df['Region'].dropna().unique():
    region_df = df[df['Region'] == region]

    for month in months:
        month_end = month.end_time

        active = (
            (region_df['Created'] <= month_end) &
            (
                region_df['Expired'].isna() |
                (region_df['Expired'] > month_end)
            )
        ).sum()

        results.append({
            'Region': region,
            'Month': month.to_timestamp(),
            'Active Customers': active
        })

active_endofmonth_df = pd.DataFrame(results)

plt.figure(figsize=(12, 6))

for region in active_endofmonth_df['Region'].unique():
    d = active_endofmonth_df[active_endofmonth_df['Region'] == region]
    plt.plot(d['Month'], d['Active Customers'], label=region)

plt.title('Active Customers Over Time by Region')
plt.xlabel('Month')
plt.ylabel('Active Customers')
plt.legend(title='Region')
plt.grid(True, alpha=0.3)
plt.xticks(rotation=45)
plt.tight_layout()
plt.savefig(os.path.join(output_dir, "4_active_customers.png"))
plt.close()

active_export = active_endofmonth_df.copy()
active_export['Month'] = active_export['Month'].dt.strftime('%Y-%m')

with open(os.path.join(output_dir, "4_active_customers.json"), "w") as f:
    json.dump(active_export.to_dict(orient='records'), f, indent=4)

# Graph 5 (Active Customers & Churn Percentage)
results = []

for region in df['Region'].dropna().unique():
    region_df = df[df['Region'] == region]

    for month in months:
        month_end = month.end_time

        active = (
            (region_df['Created'] <= month.start_time) &
            (
                region_df['Expired'].isna() |
                (region_df['Expired'] > month_end)
            )
        ).sum()

        results.append({
            'Region': region,
            'Month': month.to_timestamp(),
            'Active Customers': active
        })

active_df = pd.DataFrame(results)

# Customers who churned (Expired is not NaT)
df_churn = df[df['Expired'].notna()].copy()

# Month they churned
df_churn['ExpiredMonth'] = df_churn['Expired'].dt.to_period('M')

# Ensure 'Month_Period' is in active_df for grouping
active_df['Month_Period'] = active_df['Month'].dt.to_period('M')

# Calculate active customers by region and month
active_customers_by_region_month = (
    active_df.groupby(['Region', 'Month_Period'])['Active Customers'].sum()
)

# Calculate churned customers by region and month
# First, ensure df_churn has 'Region' and 'ExpiredMonth' as a Period object
# df_churn already has 'ExpiredMonth' from a previous cell, assume it's Period('M')
churned_customers_by_region_month = (
    df_churn.groupby(['Region', 'ExpiredMonth']).size()
)

# Align index name for merging/joining later
churned_customers_by_region_month.index.names = ['Region', 'Month_Period']

# Combine the churned customers and active customers data
# Use unstack to easily combine, then stack back for calculation
regional_churn_metrics_df = pd.DataFrame({
    'Churned Customers': churned_customers_by_region_month,
    'Active Customers (start of month)': active_customers_by_region_month
}).unstack(fill_value=0).stack()

# Handle potential NaN from unstack/stack if a region/month combination only exists in one series
regional_churn_metrics_df['Churned Customers'] = regional_churn_metrics_df['Churned Customers'].fillna(0)
regional_churn_metrics_df['Active Customers (start of month)'] = regional_churn_metrics_df['Active Customers (start of month)'].fillna(0)

# Drop months/regions where there are no active customers to avoid division by zero
regional_churn_metrics_df = regional_churn_metrics_df[
    regional_churn_metrics_df['Active Customers (start of month)'] > 0
]

# Calculate monthly churn percentage by region
regional_churn_metrics_df['Monthly Churn Percentage'] = (
    (regional_churn_metrics_df['Churned Customers'] /
     regional_churn_metrics_df['Active Customers (start of month)']) * 100
)

churn_export = regional_churn_metrics_df.reset_index()
churn_export['Month'] = churn_export['Month_Period'].dt.strftime('%Y-%m')

with open(os.path.join(output_dir, "5_churn_percentage.json"), "w") as f:
    json.dump(churn_export[['Region', 'Month', 'Monthly Churn Percentage']].to_dict(orient='records'), f, indent=4)

# Graph 6 (monthly_site_churn)
# Generate monthly periods covering the dataset
months = pd.period_range(
    start=df['Created'].min().to_period('M'),
    end=eval_date.to_period('M') - 1,
    freq='M'
)

results = []

# Calculate Active Customers at the start of each month
for site in df['Site'].dropna().unique():
    site_df = df[df['Site'] == site]

    for month in months:
        month_start = month.start_time

        active = (
            (site_df['Created'] <= month_start) &
            (
                site_df['Expired'].isna() |
                (site_df['Expired'] > month_start)
            )
        ).sum()

        results.append({
            'Site': site,
            'Month_Period': month,
            'Active Customers': active
        })

active_df = pd.DataFrame(results)

# Calculate Churned Customers
df_churn_site = df[df['Expired'].notna()].copy()
df_churn_site['Created_Period'] = df_churn_site['Created'].dt.to_period('M')
df_churn_site['Expired_Period'] = df_churn_site['Expired'].dt.to_period('M')

# EXCLUDE same-month churners: Created month must NOT equal Expired month
df_retained_churn = df_churn_site[df_churn_site['Created_Period'] != df_churn_site['Expired_Period']]

# Group churned counts by Site and Expired Month
churned_customers_by_site_month = (
    df_retained_churn.groupby(['Site', 'Expired_Period']).size()
)

# Active customers by Site and Month
active_customers_by_site_month = (
    active_df.groupby(['Site', 'Month_Period'])['Active Customers'].sum()
)

# Combine Metrics
site_churn_metrics_df = pd.DataFrame({
    'Churned Customers': churned_customers_by_site_month,
    'Active Customers': active_customers_by_site_month
}).unstack(fill_value=0).stack()

site_churn_metrics_df['Churned Customers'] = site_churn_metrics_df['Churned Customers'].fillna(0)
site_churn_metrics_df['Active Customers'] = site_churn_metrics_df['Active Customers'].fillna(0)

# Remove months with no active baseline customers
site_churn_metrics_df = site_churn_metrics_df[
    site_churn_metrics_df['Active Customers'] > 0
]

# Calculate monthly churn percentage
site_churn_metrics_df['Monthly Churn Percentage'] = (
    site_churn_metrics_df['Churned Customers']
    /
    site_churn_metrics_df['Active Customers']
) * 100

# Map Site to Region from the original dataset
site_region_map = df.dropna(subset=['Site', 'Region']).drop_duplicates('Site').set_index('Site')['Region'].to_dict()

site_churn_metrics_df.index.names = ['Site', 'Month_Period']
site_export = site_churn_metrics_df.reset_index()
site_export['Month'] = site_export['Month_Period'].dt.strftime('%Y-%m')
site_export['Region'] = site_export['Site'].map(site_region_map)

# Export JSON for the weekly.blade.php / site churn dashboard tab
with open(os.path.join(output_dir, "6_site_monthly_churn.json"), "w") as f:
    json.dump(
        site_export[['Region', 'Site', 'Month', 'Monthly Churn Percentage', 'Active Customers', 'Churned Customers']].to_dict(orient='records'),
        f,
        indent=4
    )

# ============================================================
# GRAPH 6B - Same-Month Cancellations (Early Churn)
# ============================================================

# Filter for customers who were Created AND Expired in the same calendar month
df_same_month = df[df['Expired'].notna()].copy()
df_same_month['Created_Period'] = df_same_month['Created'].dt.to_period('M')
df_same_month['Expired_Period'] = df_same_month['Expired'].dt.to_period('M')

# Keep only same-month drop-offs
df_same_month = df_same_month[df_same_month['Created_Period'] == df_same_month['Expired_Period']]

# Total new signups per site per month (denominator)
df['Created_Period'] = df['Created'].dt.to_period('M')
new_signups_by_site_month = df.groupby(['Site', 'Created_Period']).size()

# Same-month cancellations per site per month (numerator)
same_month_cancels_by_site_month = df_same_month.groupby(['Site', 'Expired_Period']).size()

# Combine into a single DataFrame
same_month_metrics_df = pd.DataFrame({
    'Same Month Cancellations': same_month_cancels_by_site_month,
    'New Signups': new_signups_by_site_month
}).unstack(fill_value=0).stack()

same_month_metrics_df['Same Month Cancellations'] = same_month_metrics_df['Same Month Cancellations'].fillna(0)
same_month_metrics_df['New Signups'] = same_month_metrics_df['New Signups'].fillna(0)

# Filter out site-months with zero new signups
same_month_metrics_df = same_month_metrics_df[same_month_metrics_df['New Signups'] > 0]

# Calculate Same-Month Cancellation Rate
same_month_metrics_df['Same Month Cancellation Rate'] = (
    same_month_metrics_df['Same Month Cancellations'] / same_month_metrics_df['New Signups']
) * 100

# Fix index names and prepare for export
same_month_metrics_df.index.names = ['Site', 'Month_Period']
same_month_export = same_month_metrics_df.reset_index()

same_month_export['Month'] = same_month_export['Month_Period'].dt.strftime('%Y-%m')
same_month_export['Region'] = same_month_export['Site'].map(site_region_map)

# Filter up to latest_data_month
same_month_export = same_month_export[same_month_export['Month_Period'] <= (eval_date.to_period('M') - 1)]

# Export JSON
with open(os.path.join(output_dir, "6b_same_month_cancellations.json"), "w") as f:
    json.dump(
        same_month_export[[
            'Region', 'Site', 'Month', 
            'Same Month Cancellation Rate', 'Same Month Cancellations', 'New Signups'
        ]].to_dict(orient='records'),
        f,
        indent=4
    )

# ============================================================
# GRAPH 7 - Latest Renewal Value of Currently Active Customers
# ============================================================

active_df = df[df['Status Customer'] == 'Aktif'].copy()

active_df['Price'] = pd.to_numeric(active_df['Price'], errors='coerce').fillna(0)
active_df['SellerFee'] = pd.to_numeric(active_df['SellerFee'], errors='coerce').fillna(0)
active_df['Net_Revenue'] = active_df['Price'] - active_df['SellerFee']
active_df['Renewed'] = pd.to_datetime(active_df['Renewed'], errors='coerce')

# ------------------------------------------------------------
# Keep newest renewal for each customer
# ------------------------------------------------------------

if 'Customer ID' in active_df.columns:
    
    idx = active_df.groupby('Customer ID')['Renewed'].idxmax()
    newest_active = active_df.loc[idx].copy()

else:

    newest_active = active_df.copy()

newest_active = newest_active.dropna(subset=['Region', 'Renewed']).copy()

# ------------------------------------------------------------
# Create date columns ONCE
# ------------------------------------------------------------

newest_active['Year'] = newest_active['Renewed'].dt.year.astype(str)

newest_active['MonthPeriod'] = newest_active['Renewed'].dt.to_period('M')
newest_active['Month'] = newest_active['MonthPeriod'].dt.strftime('%b %Y')

current_year = str(eval_date.year)
current = newest_active[newest_active['Year'] == current_year].copy()

week_start = current['Renewed'].min().to_period('W').start_time

current['Week'] = (
    ((current['Renewed'] - week_start).dt.days // 7) + 1
).astype(int)

def save_revenue_chart(data, x, y, filename, title, show_labels=True):

    plt.figure(figsize=(10,5))

    ax = sns.barplot(
        data=data,
        x=x,
        y=y,
        palette='Blues_d'
    )

    plt.title(title)

    plt.xlabel(x)

    plt.ylabel("Net Revenue")

    ax.yaxis.set_major_formatter('{x:,.0f}')

    if show_labels:
        for p in ax.patches:
            ax.annotate(
                f'{p.get_height():,.0f}',
                (p.get_x() + p.get_width() / 2., p.get_height()),
                ha='center',
                va='bottom',
                xytext=(0, 5),
                textcoords='offset points',
                fontsize=9,
                fontweight='bold'
            )

    plt.xticks(rotation=45)

    plt.tight_layout()

    plt.savefig(os.path.join(output_dir, filename))

    plt.close()

yearly_chart = (
    newest_active
    .groupby('Year', as_index=False)['Net_Revenue']
    .sum()
)

monthly_chart = (
    current
    .groupby('MonthPeriod', as_index=False)['Net_Revenue']
    .sum()
    .sort_values('MonthPeriod')
)

monthly_chart['Month'] = monthly_chart['MonthPeriod'].dt.strftime('%b %Y')

weekly_chart = (
    current
    .groupby('Week', as_index=False)['Net_Revenue']
    .sum()
    .sort_values('Week')
)

# Yearly
save_revenue_chart(
    yearly_chart,
    'Year',
    'Net_Revenue',
    '7_active_revenue_all.png',
    'Distribution of Current Active Revenue by Year',
    show_labels=True
)

# Monthly
save_revenue_chart(
    monthly_chart,
    'Month',
    'Net_Revenue',
    '7_active_revenue_monthly.png',
    'Latest Active Renewals by Month',
    show_labels=True
)

# Weekly
save_revenue_chart(
    weekly_chart,
    'Week',
    'Net_Revenue',
    '7_active_revenue_weekly.png',
    'Latest Active Renewals by Week',
    show_labels=False
)

def period_summary(frame, column):

    return (
        frame
        .groupby([column,'Region'])
        .agg(
            Active_Customers=('Net_Revenue','count'),
            Total_Revenue=('Net_Revenue','sum'),
            Average_Revenue=('Net_Revenue','mean')
        )
        .round(2)
        .reset_index()
        .sort_values([column,'Region'], ascending=[False,True])
        .to_dict('records')
    )

summary_revenue = (
    newest_active
    .groupby('Region')
    .agg(
        Active_Customers=('Net_Revenue','count'),
        Total_Revenue=('Net_Revenue','sum'),
        Average_Revenue=('Net_Revenue','mean')
    )
    .round(2)
    .sort_values('Total_Revenue', ascending=False)
    .to_dict()
)

with open(os.path.join(output_dir,'7_active_revenue.json'),'w') as f:

    json.dump(summary_revenue,f,indent=4)

# ============================================================
# MONTHLY SUMMARY
# ============================================================

with open(os.path.join(output_dir,'7_active_revenue_monthly.json'),'w') as f:

    json.dump(period_summary(current,'Month'),f,indent=4)

# ============================================================
# WEEKLY SUMMARY
# ============================================================

with open(os.path.join(output_dir,'7_active_revenue_weekly.json'),'w') as f:

    json.dump(period_summary(current,'Week'),f,indent=4)

# Graph 8 - Contract Duration vs Price

# ------------------------------------------------------------
# Prepare data
# ------------------------------------------------------------

active = df[
    (df['Status Customer'] == 'Aktif') &
    (df['Invoice_to_Expiry'].notna())
].copy()

active['Invoice_to_Expiry'] = pd.to_numeric(
    active['Invoice_to_Expiry'],
    errors='coerce'
)

active['Price'] = pd.to_numeric(
    active['Price'],
    errors='coerce'
)

active = active.dropna(
    subset=['Invoice_to_Expiry', 'Price']
)

# ------------------------------------------------------------
# Correlation
# ------------------------------------------------------------

x = active['Invoice_to_Expiry'].values
y = active['Price'].values

if len(active) >= 2 and x.std() > 0 and y.std() > 0:

    correlation, p_value = pearsonr(x, y)

    # --------------------------------------------------------
    # Linear regression
    # --------------------------------------------------------

    model = LinearRegression()

    model.fit(
        x.reshape(-1, 1),
        y
    )

    y_pred = model.predict(
        x.reshape(-1, 1)
    )

    r_squared = model.score(
        x.reshape(-1, 1),
        y
    )

    slope = model.coef_[0]
    intercept = model.intercept_

else:

    correlation = None
    p_value = None
    r_squared = None
    slope = None
    intercept = None
    y_pred = None

# ------------------------------------------------------------
# Determine relationship strength
# ------------------------------------------------------------

if correlation is not None:

    abs_r = abs(correlation)

    if abs_r >= 0.7:
        strength = "Strong"
    elif abs_r >= 0.3:
        strength = "Moderate"
    elif abs_r >= 0.1:
        strength = "Weak"
    else:
        strength = "Very Weak"

    if correlation > 0:
        direction = "Positive"
    elif correlation < 0:
        direction = "Negative"
    else:
        direction = "None"

    relationship = f"{strength} {direction}"

    statistically_significant = p_value < 0.05

else:

    relationship = "Insufficient data"
    statistically_significant = False

# ============================================================
# SAVE GRAPH
# ============================================================

plt.figure(figsize=(12, 7))

# ------------------------------------------------------------
# Plot clusters
# ------------------------------------------------------------

if 'Cluster' in active.columns:

    for cluster in sorted(active['Cluster'].dropna().unique()):

        cluster_data = active[
            active['Cluster'] == cluster
        ]

        if cluster == -1:
            label = "Noise"
        else:
            label = f"Cluster {int(cluster)}"

        plt.scatter(
            cluster_data['Invoice_to_Expiry'],
            cluster_data['Price'],
            s=40,
            alpha=0.2,
            label=label
        )

else:

    plt.scatter(
        active['Invoice_to_Expiry'],
        active['Price'],
        s=40,
        alpha=0.2,
        label='Customers'
    )

# ------------------------------------------------------------
# Regression line
# ------------------------------------------------------------

if y_pred is not None:

    sort_idx = np.argsort(x)

    plt.plot(
        x[sort_idx],
        y_pred[sort_idx],
        linewidth=2,
        label=(
            f"Linear fit "
            f"(r = {correlation:.2f}, "
            f"R² = {r_squared:.2f})"
        )
    )

# ------------------------------------------------------------
# Labels
# ------------------------------------------------------------

plt.xlabel(
    "Invoice to Expiry (days)",
    fontsize=12,
    fontweight="bold"
)

plt.ylabel(
    "Price",
    fontsize=12,
    fontweight="bold"
)

plt.title(
    "Contract Duration vs Price",
    fontsize=14,
    pad=15
)

plt.legend()
plt.grid(True, alpha=0.3)

plt.tight_layout()

plt.savefig(os.path.join(output_dir, "8_duration_vs_price.png"))

plt.close()

# ============================================================
# GRAPH 8 SUMMARY
# ============================================================

def safe_round(value, decimals=2):
    """Returns None instead of NaN so json.dump produces valid JSON (null)."""
    if value is None or pd.isna(value):
        return None
    return round(float(value), decimals)

summary_duration_price = {

    "Customers": int(len(active)),

    "Contract_Duration": {
        "Average_Days": safe_round(active['Invoice_to_Expiry'].mean()),
        "Median_Days": safe_round(active['Invoice_to_Expiry'].median()),
        "Minimum_Days": safe_round(active['Invoice_to_Expiry'].min()),
        "Maximum_Days": safe_round(active['Invoice_to_Expiry'].max()),
        "Std_Deviation": safe_round(active['Invoice_to_Expiry'].std())
    },

    "Price": {
        "Average": safe_round(active['Price'].mean()),
        "Median": safe_round(active['Price'].median()),
        "Minimum": safe_round(active['Price'].min()),
        "Maximum": safe_round(active['Price'].max()),
        "Std_Deviation": safe_round(active['Price'].std())
    },

    "Correlation": {
        "Pearson_R": safe_round(correlation, 4),
        "R_Squared": safe_round(r_squared, 4),
        "P_Value": (
            float(p_value)
            if p_value is not None and not pd.isna(p_value)
            else None
        )
    },

    "Regression": {
        "Slope": safe_round(slope, 4),
        "Intercept": safe_round(intercept, 2)
    },

    "Interpretation": {
        "Relationship": relationship,
        "Statistically_Significant": bool(statistically_significant)
    }
}

# ------------------------------------------------------------
# Save JSON
# ------------------------------------------------------------

with open(os.path.join(output_dir, "8_duration_vs_price.json"), "w") as f:
    json.dump(summary_duration_price, f, indent=4, allow_nan=False)

# ============================================================
# GRAPH 9 - Cohort LTV Curves (Avg. Cumulative Revenue by Months Since Signup)
# ============================================================

# 'Customer ID' is the column used elsewhere in this script (Graph 7).
# Falling back to 'CustomerId' in case a dataset ever uses that spelling instead.
CUSTOMER_ID_COL = 'Customer ID' if 'Customer ID' in df.columns else 'CustomerId'
MIN_COHORT_SIZE = 10

# Helper Function for Exact Calendar Months
def calc_calendar_months(start_col, end_col):
    months = (end_col.dt.year - start_col.dt.year) * 12 + (end_col.dt.month - start_col.dt.month)
    not_reached = end_col.dt.day < start_col.dt.day
    return months - not_reached.astype(int)

# Clean Data & Deduplicate to 1 Row per Customer
customer_df = df.dropna(subset=[CUSTOMER_ID_COL, 'Created', 'Expired']).copy()
customer_df['Price'] = pd.to_numeric(customer_df['Price'], errors='coerce').fillna(0)

# Keep the latest record per customer if duplicate rows exist
customer_df = customer_df.sort_values('Created').groupby(CUSTOMER_ID_COL).last().reset_index()

# Calculate Billing Cycle & Total Tenure
# Primary cycle: InvoiceDate to Expired
if 'InvoiceDate' in customer_df.columns:
    primary_cycle = calc_calendar_months(customer_df['InvoiceDate'], customer_df['Expired'])
else:
    primary_cycle = pd.Series(0, index=customer_df.index)

needs_fallback = primary_cycle < 1
customer_df['BillingCycle'] = primary_cycle

# Fallback cycle: Renewed to Expired
if needs_fallback.any() and 'Renewed' in customer_df.columns:
    fallback = calc_calendar_months(customer_df.loc[needs_fallback, 'Renewed'], customer_df.loc[needs_fallback, 'Expired'])
    customer_df.loc[needs_fallback, 'BillingCycle'] = np.maximum(1, fallback)

customer_df['BillingCycle'] = np.maximum(1, customer_df['BillingCycle'])

# Cohort month & total lifetime tenure
customer_df['CohortMonth'] = customer_df['Created'].dt.to_period('M')
customer_df['TenureMonths'] = calc_calendar_months(customer_df['Created'], customer_df['Expired'])
customer_df['TenureMonths'] = np.maximum(customer_df['TenureMonths'], customer_df['BillingCycle'])

# Discrete billing: ceil handles upfront payments per cycle
customer_df['TotalCycles'] = np.ceil(customer_df['TenureMonths'] / customer_df['BillingCycle'])

# Construct Full Cohort Grid
eval_period = eval_date.to_period('M')
customer_df['MaxObservablePeriod'] = (
    (eval_period.year - customer_df['CohortMonth'].dt.year) * 12 +
    (eval_period.month - customer_df['CohortMonth'].dt.month)
)
customer_df = customer_df[customer_df['MaxObservablePeriod'] >= 0]

n_periods = (customer_df['MaxObservablePeriod'].values)
block_starts = np.cumsum(n_periods) - n_periods
period_index = np.arange(n_periods.sum()) - np.repeat(block_starts, n_periods)

grid = pd.DataFrame({
    CUSTOMER_ID_COL: np.repeat(customer_df[CUSTOMER_ID_COL].values, n_periods),
    'CohortMonth': np.repeat(customer_df['CohortMonth'].values, n_periods),
    'CohortPeriod': period_index,
})

# Merge customer metadata back onto grid
grid = grid.merge(
    customer_df[[CUSTOMER_ID_COL, 'BillingCycle', 'TotalCycles', 'Price']],
    on=CUSTOMER_ID_COL,
    how='left'
)

# Calculate Discrete LTV per Cohort Period
# Number of billing cycles reached at month t (CohortPeriod = 0 is month 1)
cycles_billed_so_far = np.ceil((grid['CohortPeriod'] + 1) / grid['BillingCycle'])

# Cap completed cycles at the customer's total lifetime cycles (after cancellation)
completed_cycles = np.minimum(cycles_billed_so_far, grid['TotalCycles'])

# Compute discrete cumulative LTV
grid['CumulativeLTV'] = completed_cycles * grid['Price']

# Aggregate LTV Curves per Cohort
cohort_curve = (
    grid.groupby(['CohortMonth', 'CohortPeriod'])
    .agg(
        Avg_Cumulative_LTV=('CumulativeLTV', 'mean'),
        Customers=('CumulativeLTV', 'count')
    )
    .reset_index()
)

# Filter out small cohorts
cohort_sizes = customer_df.groupby('CohortMonth')[CUSTOMER_ID_COL].nunique()
valid_cohorts = cohort_sizes[cohort_sizes >= MIN_COHORT_SIZE].index
cohort_curve = cohort_curve[cohort_curve['CohortMonth'].isin(valid_cohorts)]

cohort_curve['CohortMonth'] = cohort_curve['CohortMonth'].astype(str)
cohort_curve['Avg_Cumulative_LTV'] = cohort_curve['Avg_Cumulative_LTV'].round(2)
cohort_curve = cohort_curve.sort_values(['CohortMonth', 'CohortPeriod'])

# Export
with open(os.path.join(output_dir, "9_cohort_ltv.json"), "w") as f:
    json.dump(cohort_curve.to_dict(orient='records'), f, indent=4)

# ============================================================
# DASHBOARD SUMMARY
# ============================================================

oldest_dataset = df["Renewed"].min()
latest_dataset = df["Renewed"].max()

summary_dashboard = {
    "Dataset_Range": f"{original_df['Renewed'].min():%d %b %Y} - {original_df['Renewed'].max():%d %b %Y}",
    "Entry_Count": len(original_df),

    "Region_Counts": (
        original_df["Region"]
        .value_counts()
        .reindex(["JAKARTA", "SUKABUMI", "BANDUNG"], fill_value=0)
        .to_dict()
    ),

    "Data_Quality": {
        "Rows_Used": len(df),
        "Rows_Total": len(original_df),
        "Score": round(len(df) / len(original_df) * 100, 1)
    }
}

with open(os.path.join(output_dir, "0_dashboard_cards.json"), "w") as f:
        json.dump(summary_dashboard, f, indent=4)
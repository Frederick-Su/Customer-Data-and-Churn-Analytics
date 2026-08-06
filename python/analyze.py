import sys
import os
import pandas as pd
import matplotlib.pyplot as plt
from statsmodels.graphics.mosaicplot import mosaic
import seaborn as sns
import numpy as np
import json
import matplotlib.dates as mdates

excel_path = sys.argv[1]
analysis_id = sys.argv[2]

df = pd.read_excel(excel_path)

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

# Format and find Service Duration
df['Created'] = pd.to_datetime(df['Created'])
df['Renewed'] = pd.to_datetime(df['Renewed'])

df['Expired'] = df['Expired'].replace('0000-00-00 00:00:00', np.nan)
df['Expired'] = pd.to_datetime(df['Expired'])

# Calculate Duration_days based on Status Customer
eval_date = pd.to_datetime('2026-08-02').normalize()

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

# Graph 1 (mosaic.png)
plt.rcParams["figure.figsize"] = [10.00, 3.50]
plt.rcParams["figure.autolayout"] = True
mosaic(df, index=['Region', 'Status Customer'], title='Mosaic Plot')
plt.savefig(os.path.join(output_dir, "1_mosaic.png"))
plt.close()

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

# Graph 3 (site_loyalty.png)
df['Tenure_months'] = df['Duration_days'] / 30
df = df.replace('Griya Artha Sepatan', 'GRIYA ARTHA SEPATAN')

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
    end=pd.Period('2026-07', freq='M'),
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

# Graph 5 (churn_percentage.png)
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

# Plot the monthly churn percentage by region
plt.figure(figsize=(22, 12))
ax = plt.gca() # Get current axes

for region in regional_churn_metrics_df.index.get_level_values('Region').unique():
    subset = regional_churn_metrics_df.xs(region, level='Region')
    plt.plot(subset.index.to_timestamp(), subset['Monthly Churn Percentage'], label=region)

plt.title('Monthly Churn Percentage Over Time by Region')
plt.xlabel('Month')
plt.ylabel('Churn Percentage (%)')
plt.legend(title='Region')

# Set minor ticks for monthly intervals
ax.xaxis.set_minor_locator(mdates.MonthLocator())

# Add major and minor grid lines
plt.grid(True, which='major', linestyle='-', alpha=0.7) # Major grid lines
plt.grid(True, which='minor', linestyle=':', alpha=0.5) # Minor grid lines

plt.xticks(rotation=45, ha='right')
plt.tight_layout()
plt.savefig(os.path.join(output_dir, "5_churn_percentage.png"))
plt.close()

# Graph 6 (weekly_site_churn.png) | THIS GRAPH SHOWS THE PAST 6 MONTHS FROM THE LATEST DATA |

# Generate weekly periods covering the dataset
weeks = pd.period_range(
    start=df['Created'].min().to_period('W'),
    end=max(
        df['Expired'].max() if df['Expired'].notna().any() else df['Created'].max(),
        df['Created'].max()
    ).to_period('W'),
    freq='W'
)

results = []

# Calculate active customers at the start of each week
for site in df['Site'].dropna().unique():

    site_df = df[df['Site'] == site]

    for week in weeks:

        week_start = week.start_time
        week_end = week.end_time

        active = (
            (site_df['Created'] <= week_start) &
            (
                site_df['Expired'].isna() |
                (site_df['Expired'] > week_end)
            )
        ).sum()

        results.append({
            'Site': site,
            'Week': week.start_time,
            'Active Customers': active
        })


active_df = pd.DataFrame(results)


# Customers who churned
df_churn = df[df['Expired'].notna()].copy()
df_churn['Week_Period'] = df_churn['Expired'].dt.to_period('W')

active_df['Week_Period'] = active_df['Week'].dt.to_period('W')


# Active customers by site and week
active_customers_by_site_week = (
    active_df.groupby(['Site', 'Week_Period'])['Active Customers']
    .sum()
)


# Churned customers by site and week
churned_customers_by_site_week = (
    df_churn.groupby(['Site', 'Week_Period'])
    .size()
)


# Combine
site_churn_metrics_df = pd.DataFrame({
    'Churned Customers': churned_customers_by_site_week,
    'Active Customers': active_customers_by_site_week
}).unstack(fill_value=0).stack()


site_churn_metrics_df['Churned Customers'] = (
    site_churn_metrics_df['Churned Customers'].fillna(0)
)

site_churn_metrics_df['Active Customers'] = (
    site_churn_metrics_df['Active Customers'].fillna(0)
)


# Remove weeks with no active customers
site_churn_metrics_df = site_churn_metrics_df[
    site_churn_metrics_df['Active Customers'] > 0
]


# Calculate weekly churn percentage
site_churn_metrics_df['Weekly Churn Percentage'] = (
    site_churn_metrics_df['Churned Customers']
    /
    site_churn_metrics_df['Active Customers']
) * 100

# Keep only the last 6 completed months
latest_month = df['Created'].max().to_period('M')
# Keep only the last 6 completed months based on available data
# Exclude current month and future dates

today_month = pd.Timestamp.today().to_period('M')

# Latest month that actually has passed
latest_data_month = min(
    df['Created'].max().to_period('M'),
    today_month - 1
)

first_month = latest_data_month - 5
week_months = (
    site_churn_metrics_df
    .index
    .get_level_values('Week_Period')
    .to_timestamp()
    .to_period('M')
)

site_churn_metrics_df = site_churn_metrics_df[
    (week_months >= first_month) &
    (week_months <= latest_data_month)
]

# Plot one graph per site
for site in site_churn_metrics_df.index.get_level_values('Site').unique():

    subset = site_churn_metrics_df.xs(site, level='Site').copy()


    # Ignore weeks before the site existed
    site_created = (
        df.loc[df['Site'] == site, 'Created']
        .min()
        .to_period('W')
    )

    subset = subset[subset.index >= site_created]


    plt.figure(figsize=(12, 5))
    ax = plt.gca()


    plt.plot(
        subset.index.to_timestamp(),
        subset['Weekly Churn Percentage'],
        marker='o',
        linewidth=2
    )


    plt.title(f'Weekly Churn Percentage - {site}')
    plt.xlabel('Month')
    plt.ylabel('Churn Percentage (%)')

    # Show month labels
    ax.xaxis.set_major_locator(mdates.MonthLocator())
    ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))


    plt.grid(True, alpha=0.7)
    plt.xticks(rotation=45, ha='right')
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, f"6_{site}_weekly_churn.png"))
    plt.close()
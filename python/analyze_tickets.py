import sys
import os
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
import json
import matplotlib.ticker as ticker

# ============================================================
# SETUP & FILE READING
# ============================================================
excel_path = sys.argv[1]
analysis_id = sys.argv[2]

file_ext = os.path.splitext(excel_path)[1].lower()

# Expected ticketing table headers
EXPECTED_COLUMNS = [
    'No',
    'Ticket ID',
    'Creation Time',
    'VN ID',
    'Name',
    'Address',
    'Kontak PIC',
    'Complaint',
    'Type Complaint',
    'Complaint Category',
    'Area',
    'Description',
    'NOC',
    'Status',
    'Close Time',
    'Duration',
    'Action Case'
]

def find_header_row(raw_df):
    """
    Find the row containing the actual ticket table headers.

    We look for several distinctive columns rather than requiring
    every column to match, making the detection more tolerant of
    minor changes in the source file.
    """
    required_headers = {
        'Ticket ID',
        'Creation Time',
        'VN ID',
        'Type Complaint',
        'Area'
    }

    for row_idx in range(len(raw_df)):
        row_values = set(
            str(value).strip()
            for value in raw_df.iloc[row_idx].tolist()
            if pd.notna(value)
        )

        matches = required_headers.intersection(row_values)

        # Require all distinctive headers to be present
        if matches == required_headers:
            return row_idx

    return None


# ------------------------------------------------------------
# Read raw file first so we can detect the real header row
# ------------------------------------------------------------
if file_ext == '.csv':
    try:
        raw_df = pd.read_csv(
            excel_path,
            header=None,
            encoding='utf-8'
        )
    except UnicodeDecodeError:
        raw_df = pd.read_csv(
            excel_path,
            header=None,
            encoding='latin1'
        )

elif file_ext in ['.xlsx', '.xls']:
    raw_df = pd.read_excel(
        excel_path,
        header=None
    )

else:
    raise ValueError(
        f"Unsupported file format '{file_ext}'. "
        "Please provide a .xlsx, .xls, or .csv file."
    )


# ------------------------------------------------------------
# Detect actual table header
# ------------------------------------------------------------
header_row = find_header_row(raw_df)

if header_row is None:
    raise ValueError(
        "Could not detect the ticket table header. "
        "Expected columns such as 'Ticket ID', 'Creation Time', "
        "'VN ID', 'Type Complaint', and 'Area'."
    )

print(f"Detected ticket table header at row {header_row + 1}")


# ------------------------------------------------------------
# Extract actual table
# ------------------------------------------------------------
df = raw_df.iloc[header_row + 1:].copy()

# Use the detected header row as column names
df.columns = [
    str(column).strip()
    for column in raw_df.iloc[header_row].tolist()
]

# Remove completely empty rows
df = df.dropna(how='all').reset_index(drop=True)

# ------------------------------------------------------------
# Validate expected columns
# ------------------------------------------------------------
missing_columns = [
    column
    for column in EXPECTED_COLUMNS
    if column not in df.columns
]

if missing_columns:
    raise ValueError(
        f"Detected table header, but these expected columns are missing: "
        f"{missing_columns}"
    )

# Keep only the expected ticketing columns and preserve their order
df = df[EXPECTED_COLUMNS]

original_df = df.copy()

# Define the project root and output directory (storage/app/public/results)
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

# ============================================================
# PRE-PROCESSING
# ============================================================
df['VN ID'] = df['VN ID'].replace('-', np.nan)
df['Creation Time'] = pd.to_datetime(df['Creation Time'], errors='coerce')

if 'Duration' in df.columns:
    df['Duration_Days'] = pd.to_timedelta(df['Duration'], errors='coerce').dt.total_seconds() / (24 * 3600)

def safe_int(val):
    return int(val) if pd.notna(val) else 0

def safe_float(val):
    return round(float(val), 2) if pd.notna(val) else 0.0

# ============================================================
# 0. DASHBOARD SUMMARY
# ============================================================
summary_dashboard = {
    "Total_Rows": len(original_df),
    "Unique_Tickets": safe_int(original_df["Ticket ID"].nunique()),
    "Duplicate_Tickets": safe_int(original_df.duplicated(subset=["Ticket ID"]).sum()),
    "Unique_VN_IDs": safe_int(df['VN ID'].nunique())
}

with open(os.path.join(output_dir, "0_dashboard_cards.json"), "w") as f:
    json.dump(summary_dashboard, f, indent=4)

# ============================================================
# 1. TICKETS BY AREA
# ============================================================
df_area = df.dropna(subset=['Area'])

area_export = pd.DataFrame({
    'Area': df_area['Area'].astype(str),
    'Date': df_area['Creation Time'].dt.strftime('%Y-%m')
})
# ticket_counts = df_area.groupby('Area')['Ticket ID'].count().reset_index(name='Ticket_Count')
# ticket_counts = ticket_counts.sort_values(by='Ticket_Count', ascending=False)

# # plt.figure(figsize=(10, 6))
# # sns.barplot(data=ticket_counts, x='Ticket_Count', y='Area', palette='viridis')
# # plt.title('Number of Tickets by Area', fontsize=14, fontweight='bold')
# # plt.xlabel('Ticket Count', fontsize=12)
# # plt.ylabel('Area', fontsize=12)
# # plt.tight_layout()
# # plt.savefig(os.path.join(output_dir, "1_tickets_by_area.png"))
# # plt.close()

# df_area = df.dropna(subset=['Area'])
# ticket_counts = df_area.groupby('Area')['Ticket ID'].count().reset_index(name='Ticket_Count')
# ticket_counts = ticket_counts.sort_values(by='Ticket_Count', ascending=False)

# Export JSON for Chart.js
with open(os.path.join(output_dir, "1_tickets_by_area.json"), "w") as f:
    json.dump(area_export.to_dict(orient='records'), f, indent=4)

# ============================================================
# 2 & 8. MONTHLY TICKET VOLUME BY COMPLAINT TYPE
# ============================================================

df_time = df.dropna(subset=['Creation Time']).copy()

if not df_time.empty:

    # Create a monthly period
    df_time['Month'] = df_time['Creation Time'].dt.to_period('M')

    # --------------------------------------------------------
    # Count tickets by month and complaint type
    # --------------------------------------------------------

    monthly_complaints = (
        df_time
        .groupby(['Month', 'Type Complaint'])['Ticket ID']
        .count()
        .reset_index(name='Ticket_Count')
    )

    # --------------------------------------------------------
    # Keep complaint types with at least 50 tickets overall
    # --------------------------------------------------------

    complaint_totals = (
        monthly_complaints
        .groupby('Type Complaint')['Ticket_Count']
        .sum()
    )

    valid_complaints = complaint_totals[
        complaint_totals >= 50
    ].index

    monthly_complaints = monthly_complaints[
        monthly_complaints['Type Complaint'].isin(valid_complaints)
    ]

    # --------------------------------------------------------
    # Total tickets per month
    #
    # This uses ALL tickets, not only complaint types that
    # passed the >= 50 overall threshold. Computed first so the
    # pivot below can be reindexed against its (complete) month
    # range.
    # --------------------------------------------------------

    monthly_total = (
        df_time
        .groupby('Month')['Ticket ID']
        .count()
        .sort_index()
    )

    # --------------------------------------------------------
    # Create a pivot table:
    #
    # Month | Complaint A | Complaint B | Complaint C | ...
    #
    # Reindexed against monthly_total's month range so a month
    # never drops out of the export just because none of its
    # tickets belonged to a complaint type that cleared the
    # >= 50 threshold (if EVERY type falls under 50, the plain
    # .pivot() collapses to zero rows and the trend chart gets
    # an empty array instead of the total-tickets line).
    # --------------------------------------------------------

    monthly_pivot = (
        monthly_complaints
        .pivot(
            index='Month',
            columns='Type Complaint',
            values='Ticket_Count'
        )
        .reindex(monthly_total.index)
        .fillna(0)
        .sort_index()
    )

    # --------------------------------------------------------
    # Convert Period index to strings for JSON
    # --------------------------------------------------------

    monthly_export = []

    for month in monthly_total.index:

        row = {
            'month': str(month),
            'total': int(monthly_total.get(month, 0))
        }

        for complaint_type in monthly_pivot.columns:
            row[complaint_type] = int(
                monthly_pivot.loc[month, complaint_type]
            )

        monthly_export.append(row)

    # --------------------------------------------------------
    # Export JSON for Chart.js
    # --------------------------------------------------------

    with open(
        os.path.join(output_dir, "monthly_complaint_trend.json"),
        "w"
    ) as f:
        json.dump(
            monthly_export,
            f,
            indent=4
        )

# ============================================================
# 3. TOP 20 VN IDs BY COMPLAINT COUNT
# ============================================================
top_20_complainers = df['VN ID'].dropna().value_counts().head(20).index
complainer_df = df[df['VN ID'].isin(top_20_complainers)]

complainer_df['Date'] = complainer_df['Creation Time'].dt.strftime('%Y-%m')
top_20_export = complainer_df[['VN ID', 'Date']].to_dict(orient='records')

with open(os.path.join(output_dir, "3_top_vn_ids.json"), "w") as f:
    json.dump(top_20_export, f, indent=4)

# ============================================================
# 4. MEDIAN DURATION BY AREA
# ============================================================
if 'Duration_Days' in df.columns:
    df_dur_area = df.dropna(subset=['Area', 'Duration']).copy()
    median_duration = df_dur_area.groupby('Area')['Duration_Days'].median().reset_index()
    median_duration = median_duration.sort_values(by='Duration_Days', ascending=False)

    plt.figure(figsize=(10, 10))
    sns.barplot(data=median_duration, x='Duration_Days', y='Area', palette='magma')
    plt.title('Median Ticket Duration by Area', fontsize=14, fontweight='bold')
    plt.xlabel('Median Duration (Days)', fontsize=12)
    plt.ylabel('Area', fontsize=12)
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, "4_median_duration_area.png"))
    plt.close()

    with open(os.path.join(output_dir, "4_median_duration_area.json"), "w") as f:
        json.dump(median_duration.fillna(0).to_dict(orient='records'), f, indent=4)

# ============================================================
# 5. DURATION SUMMARY BY TYPE COMPLAINT
# ============================================================
if 'Duration_Days' in df.columns:
    df_dur_complaint = df.dropna(subset=['Type Complaint', 'Duration']).copy()
    
    complaint_stats = df_dur_complaint.groupby('Type Complaint')['Duration_Days'].agg(
        Ticket_Count='count',
        Mean_Duration='mean',
        Median_Duration='median'
    ).reset_index()
    
    complaint_stats = complaint_stats.sort_values(by='Median_Duration', ascending=False)

    plt.figure(figsize=(12, 8))
    sns.barplot(data=complaint_stats.head(100), x='Median_Duration', y='Type Complaint', palette='coolwarm')
    plt.title('Median Ticket Resolution Duration by Type Complaint', fontsize=14, fontweight='bold')
    plt.xlabel('Median Duration (Days)', fontsize=12)
    plt.ylabel('Type Complaint', fontsize=12)
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, "5_duration_by_complaint.png"))
    plt.close()

    with open(os.path.join(output_dir, "5_duration_by_complaint.json"), "w") as f:
        json.dump(complaint_stats.fillna(0).to_dict(orient='records'), f, indent=4)

# ============================================================
# 6. TICKET DURATION DISTRIBUTION (STRIP PLOT)
# ============================================================
if 'Duration_Days' in df.columns:
    top_complaints = df_dur_complaint['Type Complaint'].value_counts().head(15).index
    df_subset = df_dur_complaint[df_dur_complaint['Type Complaint'].isin(top_complaints)]

    plt.figure(figsize=(12, 8))
    sns.stripplot(data=df_subset, x='Duration_Days', y='Type Complaint', color='black', alpha=0.3, jitter=0.2, size=5, zorder=1)
    sns.pointplot(data=df_subset, x='Duration_Days', y='Type Complaint', color='red', errorbar=None, join=False, markers='D', scale=0.75, zorder=3)
    
    plt.title('Ticket Duration by Type Complaint (with Mean Markers)', fontsize=14, fontweight='bold')
    plt.xlabel('Duration (Days)', fontsize=12)
    plt.ylabel('Type Complaint', fontsize=12)
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, "6_duration_distribution.png"))
    plt.close()

# ============================================================
# 7. PROPORTION OF TICKETS (PIE CHART)
# ============================================================
# 1. Clean the data and get exact counts for every complaint type
df_clean_pie = df.dropna(subset=['Type Complaint'])

pie_export = pd.DataFrame({
    'Type Complaint': df_clean_pie['Type Complaint'].astype(str),
    'Date': df_clean_pie['Creation Time'].dt.strftime('%Y-%m')
})

with open(os.path.join(output_dir, "7_complaint_proportion.json"), "w") as f:
    json.dump(pie_export.to_dict(orient='records'), f, indent=4)

# ============================================================
# 9. COMPLAINT CONCENTRATION HEATMAP
# ============================================================
df_heatmap = df.dropna(subset=['Area', 'Type Complaint'])
if not df_heatmap.empty:
    ct = pd.crosstab(df_heatmap['Area'], df_heatmap['Type Complaint'])
    
    plt.figure(figsize=(12, 12))
    sns.heatmap(ct, annot=True, fmt='d', cmap='Blues')
    plt.title('Concentration of Complaint Types Across Areas', fontsize=14, fontweight='bold')
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, "9_complaint_heatmap.png"))
    plt.close()

    with open(os.path.join(output_dir, "9_complaint_heatmap.json"), "w") as f:
        json.dump(ct.to_dict(orient='index'), f, indent=4)

# ============================================================
# 10. TICKETS BY COMPLAINT CATEGORY
# ============================================================
df_category = df.dropna(subset=['Complaint Category'])

category_export = pd.DataFrame({
    'Complaint Category': df_category['Complaint Category'].astype(str),
    'Date': df_category['Creation Time'].dt.strftime('%Y-%m')
})

with open(os.path.join(output_dir, "10_tickets_by_complaint_category.json"), "w") as f:
    json.dump(category_export.to_dict(orient='records'), f, indent=4)
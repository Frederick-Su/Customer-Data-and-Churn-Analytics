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

if file_ext == '.csv':
    try:
        # First, try standard UTF-8 encoding
        df = pd.read_csv(excel_path, encoding='utf-8')
    except UnicodeDecodeError:
        # If it fails (usually due to Windows Excel exports), fallback to Latin-1
        df = pd.read_csv(excel_path, encoding='latin1')
elif file_ext in ['.xlsx', '.xls']:
    df = pd.read_excel(excel_path)
else:
    raise ValueError(f"Unsupported file format '{file_ext}'. Please provide a .xlsx, .xls, or .csv file.")

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
# 2. TICKETS BY COMPLAINT TYPE
# ============================================================
# 1. Clean data, count occurrences, and reset to DataFrame
complaint_counts = df['Type Complaint'].dropna().value_counts().reset_index()
complaint_counts.columns = ['Type Complaint', 'Ticket_Count']

# 2. Drop anything below 50 counts
complaint_counts = complaint_counts[complaint_counts['Ticket_Count'] >= 50]

# 3. Plot the results
plt.figure(figsize=(10, 6))
sns.barplot(data=complaint_counts, x='Ticket_Count', y='Type Complaint', palette='magma')
plt.title('Number of Tickets by Type Complaint (>= 50 Count)', fontsize=14, fontweight='bold')
plt.xlabel('Ticket Count', fontsize=12)
plt.ylabel('Type Complaint', fontsize=12)
plt.tight_layout()
plt.savefig(os.path.join(output_dir, "2_tickets_by_complaint.png"))
plt.close()

# 4. Export to JSON
with open(os.path.join(output_dir, "2_tickets_by_complaint.json"), "w") as f:
    json.dump(complaint_counts.to_dict(orient='records'), f, indent=4)

# ============================================================
# 3. TOP 20 VN IDs BY COMPLAINT COUNT
# ============================================================
top_20_complainers = df['VN ID'].value_counts().head(20)

plt.figure(figsize=(10, 8))
ax = sns.barplot(x=top_20_complainers.values, y=top_20_complainers.index, palette='viridis')
ax.xaxis.set_major_locator(ticker.MultipleLocator(5))
ax.xaxis.set_minor_locator(ticker.MultipleLocator(1))
ax.tick_params(axis='x', which='major', length=7, width=1.5)
ax.tick_params(axis='x', which='minor', length=4, color='gray')

plt.title('Top 20 VN IDs by Complaint Count', fontsize=14, fontweight='bold')
plt.xlabel('Complaint Count', fontsize=12)
plt.ylabel('VN ID', fontsize=12)
plt.tight_layout()
plt.savefig(os.path.join(output_dir, "3_top_vn_ids.png"))
plt.close()

with open(os.path.join(output_dir, "3_top_vn_ids.json"), "w") as f:
    json.dump(top_20_complainers.to_dict(), f, indent=4)

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
# 8. MONTHLY TICKET VOLUME TREND
# ============================================================
df_time = df.dropna(subset=['Creation Time']).copy()
if not df_time.empty:
    monthly_trend = df_time.set_index('Creation Time').resample('ME')['Ticket ID'].count()
    
    plt.figure(figsize=(12, 6))
    monthly_trend.plot(kind='line', marker='o')
    plt.title('Monthly Ticket Volume Trend', fontsize=14, fontweight='bold')
    plt.xlabel('Month', fontsize=12)
    plt.ylabel('Ticket Volume', fontsize=12)
    plt.grid(True, alpha=0.3)
    plt.tight_layout()
    plt.savefig(os.path.join(output_dir, "8_monthly_volume.png"))
    plt.close()

    monthly_export = monthly_trend.reset_index()
    monthly_export['Creation Time'] = monthly_export['Creation Time'].dt.strftime('%Y-%m')
    
    with open(os.path.join(output_dir, "8_monthly_volume.json"), "w") as f:
        json.dump(monthly_export.to_dict(orient='records'), f, indent=4)

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
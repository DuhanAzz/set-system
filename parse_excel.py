import sys
import pandas as pd

try:
    df = pd.read_excel(sys.argv[1])
    print(df.head())
    print("Columns:", df.columns.tolist())
except Exception as e:
    print("Error:", e)

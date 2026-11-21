import sys
import json
import openpyxl

def parse_excel(file_path, sheet_name=None):
    try:
        wb = openpyxl.load_workbook(file_path, data_only=True, read_only=True)
    except Exception as e:
        return {"error": f"Failed to load workbook: {str(e)}"}

    target_sheet = sheet_name
    if not target_sheet or target_sheet not in wb.sheetnames:
        if 'Feuil1' in wb.sheetnames:
            target_sheet = 'Feuil1'
        else:
            target_sheet = wb.sheetnames[0]

    sheet = wb[target_sheet]
    rows = list(sheet.iter_rows(values_only=True))

    if not rows:
        return {"error": f"Sheet {target_sheet} is empty."}

    # Find the header row (look for 'الاسم' or 'الرقم الوظيفي' or row 5)
    header_idx = 4 # default row 5 (0-indexed 4)
    for idx, r in enumerate(rows[:10]):
        row_str = " ".join([str(c) for c in r if c is not None])
        if "الاسم" in row_str and ("الرقم" in row_str or "الوظيفي" in row_str):
            header_idx = idx
            break

    header_row = rows[header_idx]
    headers = []
    for i, cell in enumerate(header_row):
        if cell is not None and str(cell).strip() != "":
            headers.append(str(cell).strip())
        else:
            headers.append(f"col_{i}")

    data_rows = []
    for row_num, r in enumerate(rows[header_idx + 1:], start=header_idx + 2):
        # Check if row is not completely empty
        if not any(c is not None and str(c).strip() != "" for c in r):
            continue

        item = {"_row_number": row_num}
        for i, h in enumerate(headers):
            val = r[i] if i < len(r) else None
            if val is not None:
                val_str = str(val).strip()
                item[h] = val_str if val_str != "" else None
            else:
                item[h] = None

        # Check if at least has an ID or name
        if item.get("الاسم") or item.get("الرقم الوظيفي") or item.get("م"):
            data_rows.append(item)

    return {
        "sheet_name": target_sheet,
        "total_parsed": len(data_rows),
        "data": data_rows
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: python parse_excel.py <path_to_excel_file> [sheet_name]"}))
        sys.exit(1)

    file_path = sys.argv[1]
    sheet = sys.argv[2] if len(sys.argv) > 2 else None

    result = parse_excel(file_path, sheet)
    print(json.dumps(result, ensure_ascii=False))

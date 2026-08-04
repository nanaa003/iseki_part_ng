import os
import re

options_html = """
<option value="">Semua Divisi</option>
<optgroup label="Assembling">
    <option value="Assembling" {{ request('divisi') == 'Assembling' ? 'selected' : '' }}>Semua Assembling</option>
    <option value="mainline" {{ request('divisi') == 'mainline' ? 'selected' : '' }}>Mainline</option>
    <option value="subassy" {{ request('divisi') == 'subassy' ? 'selected' : '' }}>Sub Assy</option>
    <option value="sub engine" {{ request('divisi') == 'sub engine' ? 'selected' : '' }}>Sub Engine</option>
    <option value="inspeksi" {{ request('divisi') == 'inspeksi' ? 'selected' : '' }}>Inspeksi</option>
    <option value="mower" {{ request('divisi') == 'mower' ? 'selected' : '' }}>Repair Mower</option>
</optgroup>
<optgroup label="Painting">
    <option value="Painting" {{ request('divisi') == 'Painting' ? 'selected' : '' }}>Semua Painting</option>
    <option value="painting a" {{ request('divisi') == 'painting a' ? 'selected' : '' }}>Painting A (Line A)</option>
    <option value="painting b" {{ request('divisi') == 'painting b' ? 'selected' : '' }}>Painting B (Line B)</option>
</optgroup>
<option value="DST" {{ request('divisi') == 'DST' ? 'selected' : '' }}>DST</option>
"""

# Regex to match the select dropdown with name="divisi"
pattern = re.compile(r'(<select[^>]*name="divisi"[^>]*>)(.*?)(</select>)', re.DOTALL | re.IGNORECASE)

base_dir = r"c:\xampp\htdocs\iseki_part_ng\resources\views"
count = 0

for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file.endswith('.blade.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            if pattern.search(content):
                # We do the replacement, making sure we insert our HTML between the start and end tags
                new_content = pattern.sub(lambda m: m.group(1) + "\n" + options_html.strip() + "\n" + m.group(3), content)
                if new_content != content:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    count += 1
                    print(f"Updated {filepath}")

print(f"Total files updated: {count}")

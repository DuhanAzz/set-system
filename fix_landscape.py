import re

with open('views/core/tools/pace_calculator.php', 'r') as f:
    content = f.read()

# 1. Remove invalid @page
old_css = """        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm; 
            }
            body.is-landscape @page {
                size: A4 landscape;
            }"""

new_css = """        @media print {
            @page {
                margin: 0mm; 
            }"""

content = content.replace(old_css, new_css)

# 2. Add style injection in generateChartBulk
bulk_old = """        function generateChartBulk() {
            document.body.classList.add('is-landscape');
            
            document.body.classList.add('is-landscape');"""

bulk_new = """        function generateChartBulk() {
            document.body.classList.add('is-landscape');
            
            let printStyle = document.getElementById('dynamic-print-style');
            if (!printStyle) {
                printStyle = document.createElement('style');
                printStyle.id = 'dynamic-print-style';
                document.head.appendChild(printStyle);
            }
            printStyle.innerHTML = '@page { size: A4 landscape !important; margin: 0mm !important; }';
"""
content = content.replace(bulk_old, bulk_new)

# 3. Add style injection in generateChartSingle
single_old = """        function generateChartSingle() {
            document.body.classList.remove('is-landscape');"""
            
single_new = """        function generateChartSingle() {
            document.body.classList.remove('is-landscape');
            
            let printStyle = document.getElementById('dynamic-print-style');
            if (!printStyle) {
                printStyle = document.createElement('style');
                printStyle.id = 'dynamic-print-style';
                document.head.appendChild(printStyle);
            }
            printStyle.innerHTML = '@page { size: A4 portrait !important; margin: 0mm !important; }';
"""
content = content.replace(single_old, single_new)

with open('views/core/tools/pace_calculator.php', 'w') as f:
    f.write(content)

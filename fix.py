import re

with open('views/core/tools/pace_calculator.php', 'r') as f:
    content = f.read()

# 1. Rename bulk generateChart
bulk_old = """        const originalGenerateChart = generateChart;
        function generateChart() {
            if (!isBulkMode) {
                document.body.classList.remove('is-landscape');
                originalGenerateChart();
                return;
            }"""

bulk_new = """        function generateChartBulk() {
            document.body.classList.add('is-landscape');"""

content = content.replace(bulk_old, bulk_new)

# 2. Rename single generateChart (around line 847)
single_old = """        function generateChart() {
            const name = document.getElementById('swimmerName').value || "Tanpa Nama";"""

single_new = """        function generateChartSingle() {
            document.body.classList.remove('is-landscape');
            const name = document.getElementById('swimmerName').value || "Tanpa Nama";"""

content = content.replace(single_old, single_new)

# 3. Rename bulk resetForm
reset_bulk_old = """        const originalResetForm = resetForm;
        function resetForm() {
            if (!isBulkMode) {
                originalResetForm();
                return;
            }"""
            
reset_bulk_new = """        function resetFormBulk() {"""

content = content.replace(reset_bulk_old, reset_bulk_new)

# 4. Rename single resetForm (around line 1057)
reset_single_old = """        function resetForm() {
            document.getElementById('input-section').style.display = 'block';"""

reset_single_new = """        function resetFormSingle() {
            document.getElementById('input-section').style.display = 'block';"""

content = content.replace(reset_single_old, reset_single_new)

# 5. Rename bulk printChart
print_bulk_old = """        const originalPrintChart = printChart;
        function printChart() {
            if (!isBulkMode) {
                originalPrintChart();
                return;
            }"""
            
print_bulk_new = """        function printChartBulk() {"""

content = content.replace(print_bulk_old, print_bulk_new)

# 6. Rename single printChart (around line 1063)
print_single_old = """        function printChart() {
            const originalTitle = document.title;"""
            
print_single_new = """        function printChartSingle() {
            const originalTitle = document.title;"""

content = content.replace(print_single_old, print_single_new)


# 7. Add Dispatchers
dispatchers = """
        function generateChart() {
            if (isBulkMode) {
                generateChartBulk();
            } else {
                generateChartSingle();
            }
        }

        function resetForm() {
            if (isBulkMode) {
                resetFormBulk();
            } else {
                resetFormSingle();
            }
        }

        function printChart() {
            if (isBulkMode) {
                printChartBulk();
            } else {
                printChartSingle();
            }
        }
"""
content = content.replace("    </script>\n</body>", dispatchers + "\n    </script>\n</body>")

with open('views/core/tools/pace_calculator.php', 'w') as f:
    f.write(content)

import re

with open('views/core/tools/pace_calculator.php', 'r') as f:
    content = f.read()

# 1. Update CSS
old_css = """            body.is-landscape #print-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15mm;
                padding: 10mm 15mm !important;
            }
            .page-break { page-break-after: always; }"""

new_css = """            body.is-landscape #print-container {
                display: block;
                padding: 10mm 15mm !important;
            }
            .print-page-row {
                display: flex;
                justify-content: space-between;
                width: 100%;
                page-break-after: always;
                gap: 15mm;
                margin-bottom: 15mm;
            }
            .print-page-row:last-child {
                page-break-after: auto;
            }
            .print-col {
                width: calc(50% - 7.5mm);
            }
            .page-break { page-break-after: always; }"""

content = content.replace(old_css, new_css)

# 2. Update print generation loop
old_js_start = "            let printHtml = '';\\n\\n            rows.forEach((tr, index) => {"
new_js_start = "            let printBlocks = [];\\n\\n            rows.forEach((tr, index) => {"
content = content.replace(old_js_start.encode('utf-8').decode('unicode_escape'), new_js_start.encode('utf-8').decode('unicode_escape'))

old_p_html_start = """                // Print HTML
                let p_html = `
                    <div class="${(index % 2 === 1) ? 'page-break' : ''}" style="width: 100%;">
                        <table class="excel-table">"""
new_p_html_start = """                // Print HTML
                let p_html = `
                    <div class="print-col">
                        <table class="excel-table">"""
content = content.replace(old_p_html_start, new_p_html_start)

old_loop_end = """                p_html += `</table></div>`;
                printHtml += p_html;
            });

            printContainer.innerHTML = printHtml;"""
            
new_loop_end = """                p_html += `</table></div>`;
                printBlocks.push(p_html);
            });

            let printHtml = '';
            for (let i = 0; i < printBlocks.length; i += 2) {
                printHtml += '<div class="print-page-row">';
                printHtml += printBlocks[i];
                if (printBlocks[i+1]) {
                    printHtml += printBlocks[i+1];
                } else {
                    printHtml += '<div class="print-col"></div>';
                }
                printHtml += '</div>';
            }

            printContainer.innerHTML = printHtml;"""
content = content.replace(old_loop_end, new_loop_end)

with open('views/core/tools/pace_calculator.php', 'w') as f:
    f.write(content)

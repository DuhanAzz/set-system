import re

with open('views/core/tools/pace_calculator.php', 'r') as f:
    content = f.read()

# 1. Add buttons for 200m
content = content.replace(
    '<label class="block text-sm font-bold text-slate-700 mb-3">Waktu Tes 200 Meter</label>',
    '''<div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-bold text-slate-700">Waktu Tes 200 Meter</label>
                                <div class="flex gap-2">
                                    <button type="button" id="btn-offset-3-200" onclick="toggleOffset(3, 200)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+3</button>
                                    <button type="button" id="btn-offset-5-200" onclick="toggleOffset(5, 200)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+5</button>
                                </div>
                            </div>'''
)

# 2. Add buttons for 150m
content = content.replace(
    '<label class="block text-sm font-bold text-slate-700 mb-3">Waktu Tes 150 Meter</label>',
    '''<div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-bold text-slate-700">Waktu Tes 150 Meter</label>
                                <div class="flex gap-2">
                                    <button type="button" id="btn-offset-3-150" onclick="toggleOffset(3, 150)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+3</button>
                                    <button type="button" id="btn-offset-5-150" onclick="toggleOffset(5, 150)" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">+5</button>
                                </div>
                            </div>'''
)

# 3. Add JS toggleOffset
js_insert = '''        window.offset200 = 0;
        window.offset150 = 0;

        function toggleOffset(val, dist) {
            if (dist === 200) {
                window.offset200 = window.offset200 === val ? 0 : val;
                document.getElementById('btn-offset-3-200').className = window.offset200 === 3 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
                document.getElementById('btn-offset-5-200').className = window.offset200 === 5 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
                
                const input = document.getElementById('time200');
                if (input) input.dispatchEvent(new Event('input'));
            } else {
                window.offset150 = window.offset150 === val ? 0 : val;
                document.getElementById('btn-offset-3-150').className = window.offset150 === 3 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
                document.getElementById('btn-offset-5-150').className = window.offset150 === 5 ? "px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-bold transition" : "px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition";
                
                const input = document.getElementById('time150');
                if (input) input.dispatchEvent(new Event('input'));
            }
        }
        
'''
content = content.replace(
    '        const intensities = [',
    js_insert + '        const intensities = ['
)

# 4. formatInputAndCalcVel
format_vel_old = '''                let m = 0, s = 0, ms = 0;
                if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
                if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
                if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);

                const totalSeconds = (m * 60) + s + (ms / 100);
                const velSpan = document.getElementById(distance === 200 ? 'vel200' : 'vel150');

                if (totalSeconds > 0) {
                    const vBase = distance / totalSeconds;
                    velSpan.innerText = vBase.toFixed(3) + ' m/s';
                } else {
                    velSpan.innerText = '0.00 m/s';
                }'''

format_vel_new = '''                let m = 0, s = 0, ms = 0;
                if (val.length > 0) m = parseInt(val.substring(0, 2) || 0, 10);
                if (val.length > 2) s = parseInt(val.substring(2, 4) || 0, 10);
                if (val.length > 4) ms = parseInt(val.substring(4, 6) || 0, 10);

                let totalSeconds = (m * 60) + s + (ms / 100);
                const velSpan = document.getElementById(distance === 200 ? 'vel200' : 'vel150');

                if (totalSeconds > 0) {
                    const activeOffset = distance === 200 ? window.offset200 : window.offset150;
                    totalSeconds += (activeOffset || 0);
                    const vBase = distance / totalSeconds;
                    velSpan.innerText = vBase.toFixed(3) + ' m/s';
                } else {
                    velSpan.innerText = '0.00 m/s';
                }'''

content = content.replace(format_vel_old, format_vel_new)

# 5. generateChart
gen_old = '''            if (totalSeconds200 > 0) {
                vBase = 200 / totalSeconds200; 
                testInfo = `200m: ${formatTime(totalSeconds200)}`;
                selectedStyle = style200;
            } else {
                vBase = 150 / totalSeconds150; 
                testInfo = `150m: ${formatTime(totalSeconds150)}`;
                selectedStyle = style150;
            }

            const todayStr = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const totalDistance = totalSeconds200 > 0 ? 200 : 150;
            const totalTimeSecs = totalSeconds200 > 0 ? totalSeconds200 : totalSeconds150;
            const formattedTime = formatTime(totalTimeSecs);'''

gen_new = '''            let finalTimeSecs = 0;
            let activeOffset = 0;
            let totalDistance = 0;

            if (totalSeconds200 > 0) {
                activeOffset = window.offset200 || 0;
                finalTimeSecs = totalSeconds200 + activeOffset;
                vBase = 200 / finalTimeSecs; 
                selectedStyle = style200;
                totalDistance = 200;
            } else {
                activeOffset = window.offset150 || 0;
                finalTimeSecs = totalSeconds150 + activeOffset;
                vBase = 150 / finalTimeSecs; 
                selectedStyle = style150;
                totalDistance = 150;
            }

            const todayStr = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const formattedTime = formatTime(finalTimeSecs);
            const offsetLabel = activeOffset > 0 ? ` +${activeOffset}` : '';'''
            
content = content.replace(gen_old, gen_new)

# 6. Update Info panel string
panel_old = '<div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Waktu (detik)</div><div class="w-4">:</div><div class="flex-1 text-red-600 font-bold">${totalTimeSecs.toFixed(2).replace(\'.\', \',\')} &nbsp;&nbsp; ${formattedTime.replace(\'.\', \':\')}</div></div>'
panel_new = '<div class="flex"><div class="w-36 text-slate-500 uppercase tracking-wider text-[10px] font-bold">Total Waktu (detik)${offsetLabel}</div><div class="w-4">:</div><div class="flex-1 text-red-600 font-bold">${finalTimeSecs.toFixed(2).replace(\'.\', \',\')} &nbsp;&nbsp; ${formattedTime.replace(\'.\', \':\')}</div></div>'
content = content.replace(panel_old, panel_new)

# 7. Update Print string
print_old = '<td colspan="3" style="font-weight:bold; text-align:left;">Total Waktu (detik) : <span class="text-red">${totalTimeSecs.toFixed(2).replace(\'.\', \',\')} &nbsp;&nbsp; ${formattedTime.replace(\'.\', \':\')}</span></td>'
print_new = '<td colspan="3" style="font-weight:bold; text-align:left;">Total Waktu (detik)${offsetLabel} : <span class="text-red">${finalTimeSecs.toFixed(2).replace(\'.\', \',\')} &nbsp;&nbsp; ${formattedTime.replace(\'.\', \':\')}</span></td>'
content = content.replace(print_old, print_new)

with open('views/core/tools/pace_calculator.php', 'w') as f:
    f.write(content)

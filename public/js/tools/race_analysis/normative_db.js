/**
 * Normative Benchmarks Database & Classification Engine
 * Standar acuan SWOLF, DPS (Distance Per Stroke), dan Stroke Rate (SR)
 * berdasarkan Kelompok Umur (KU), Gender, Panjang Kolam, dan Gaya Renang.
 */

function getAgeCategory(ageVal) {
    const age = parseInt(ageVal) || 15;
    if (age <= 11) {
        return { code: 'ku4', label: 'KU 4 (≤ 11 Thn)' };
    } else if (age <= 13) {
        return { code: 'ku3', label: 'KU 3 (12 - 13 Thn)' };
    } else if (age <= 15) {
        return { code: 'ku2', label: 'KU 2 (14 - 15 Thn)' };
    } else {
        return { code: 'ku1_senior', label: 'KU 1 & Senior (≥ 16 Thn)' };
    }
}

function getNormativeBenchmarks(ageVal, gender, poolLen, stroke) {
    const age = parseInt(ageVal) || 15;
    const isFemale = (gender === 'Putri');
    const isBreaststroke = (stroke === 'Gaya Dada');
    
    let tierName = '';
    let swolfMaxSangatBaik, swolfMaxBaik, swolfMaxCukup;
    let dpsMinSangatBaik, dpsMinBaik, dpsMinCukup;
    let srMinTinggi, srMinSedang;

    if (age <= 11) {
        tierName = 'KU 4 (≤ 11 Thn)';
        if (poolLen === 50) {
            swolfMaxSangatBaik = 78;
            swolfMaxBaik = 88;
            swolfMaxCukup = 98;
        } else {
            swolfMaxSangatBaik = 40;
            swolfMaxBaik = 46;
            swolfMaxCukup = 54;
        }
        dpsMinSangatBaik = 1.25;
        dpsMinBaik = 1.05;
        dpsMinCukup = 0.90;
        
        srMinTinggi = isBreaststroke ? 42 : 52;
        srMinSedang = isBreaststroke ? 30 : 40;
    } else if (age <= 13) {
        tierName = 'KU 3 (12 - 13 Thn)';
        if (poolLen === 50) {
            swolfMaxSangatBaik = 74;
            swolfMaxBaik = 82;
            swolfMaxCukup = 92;
        } else {
            swolfMaxSangatBaik = 37;
            swolfMaxBaik = 43;
            swolfMaxCukup = 50;
        }
        dpsMinSangatBaik = 1.38;
        dpsMinBaik = 1.18;
        dpsMinCukup = 1.00;
        
        srMinTinggi = isBreaststroke ? 40 : 50;
        srMinSedang = isBreaststroke ? 28 : 38;
    } else if (age <= 15) {
        tierName = 'KU 2 (14 - 15 Thn)';
        if (poolLen === 50) {
            swolfMaxSangatBaik = 68;
            swolfMaxBaik = 76;
            swolfMaxCukup = 86;
        } else {
            swolfMaxSangatBaik = 34;
            swolfMaxBaik = 40;
            swolfMaxCukup = 47;
        }
        dpsMinSangatBaik = 1.50;
        dpsMinBaik = 1.28;
        dpsMinCukup = 1.10;
        
        srMinTinggi = isBreaststroke ? 38 : 48;
        srMinSedang = isBreaststroke ? 26 : 36;
    } else {
        tierName = 'KU 1 & Senior (≥ 16 Thn)';
        if (poolLen === 50) {
            swolfMaxSangatBaik = 64;
            swolfMaxBaik = 72;
            swolfMaxCukup = 82;
        } else {
            swolfMaxSangatBaik = 32;
            swolfMaxBaik = 38;
            swolfMaxCukup = 45;
        }
        dpsMinSangatBaik = 1.62;
        dpsMinBaik = 1.35;
        dpsMinCukup = 1.15;
        
        srMinTinggi = isBreaststroke ? 36 : 46;
        srMinSedang = isBreaststroke ? 24 : 34;
    }

    if (isFemale) {
        const swolfOffset = poolLen === 50 ? 2 : 1;
        swolfMaxSangatBaik += swolfOffset;
        swolfMaxBaik += swolfOffset;
        swolfMaxCukup += swolfOffset;

        dpsMinSangatBaik = parseFloat((dpsMinSangatBaik - 0.08).toFixed(2));
        dpsMinBaik = parseFloat((dpsMinBaik - 0.08).toFixed(2));
        dpsMinCukup = parseFloat((dpsMinCukup - 0.08).toFixed(2));
    }

    return {
        tierName,
        swolf: {
            sangatBaik: swolfMaxSangatBaik,
            baik: swolfMaxBaik,
            cukup: swolfMaxCukup,
            rujukan: `<${swolfMaxSangatBaik} Sgt Baik | ${swolfMaxSangatBaik}-${swolfMaxBaik} Baik | ${swolfMaxBaik+1}-${swolfMaxCukup} Cukup | >${swolfMaxCukup} Kurang`
        },
        dps: {
            sangatBaik: dpsMinSangatBaik,
            baik: dpsMinBaik,
            cukup: dpsMinCukup,
            rujukan: `≥${dpsMinSangatBaik.toFixed(2)}m Sgt Baik | ${dpsMinBaik.toFixed(2)}-${(dpsMinSangatBaik-0.01).toFixed(2)}m Baik | <${dpsMinCukup.toFixed(2)}m Kurang`
        },
        sr: {
            tinggi: srMinTinggi,
            sedang: srMinSedang,
            rujukan: `>${srMinTinggi} Sprint | ${srMinSedang}-${srMinTinggi} Optimal | <${srMinSedang} Tempo Rendah`
        }
    };
}

window.RaceNormative = {
    getAgeCategory,
    getNormativeBenchmarks
};

from __future__ import annotations

import zipfile
from pathlib import Path
from xml.sax.saxutils import escape


ROOT = Path(__file__).resolve().parent


def avg(values: list[float]) -> float:
    return round(sum(values) / len(values), 1) if values else 0.0


def median(values: list[float]) -> float:
    ordered = sorted(values)
    if not ordered:
        return 0.0
    middle = len(ordered) // 2
    if len(ordered) % 2:
        return round(ordered[middle], 1)
    return round((ordered[middle - 1] + ordered[middle]) / 2, 1)


def xlsx_col(index: int) -> str:
    name = ""
    while index:
        index, rem = divmod(index - 1, 26)
        name = chr(65 + rem) + name
    return name


def cell_xml(row: int, col: int, value):
    ref = f"{xlsx_col(col)}{row}"
    if value is None:
        return f'<c r="{ref}"/>'
    if isinstance(value, (int, float)) and not isinstance(value, bool):
        return f'<c r="{ref}"><v>{value}</v></c>'
    return f'<c r="{ref}" t="inlineStr"><is><t>{escape(str(value))}</t></is></c>'


def sheet_xml(rows: list[list]) -> str:
    row_xml = []
    for r, row in enumerate(rows, start=1):
        cells = "".join(cell_xml(r, c, value) for c, value in enumerate(row, start=1))
        row_xml.append(f'<row r="{r}">{cells}</row>')
    return (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        '<sheetData>'
        + "".join(row_xml)
        + '</sheetData></worksheet>'
    )


SCAN_BATCHES = {
    "B0_baseline_25s": {
        "label": "Baseline inicial",
        "reported_accuracy": "3-4/12",
        "correct": 3.5,
        "total": 12,
        "notes": "Valor inicial reportado no chat. Pipeline ainda ampla, com latencia cerca de 25s.",
        "rows": [],
    },
    "B1_primeiro_lote_lento": {
        "label": "Pipeline v2 inicial com metricas",
        "reported_accuracy": "5/14",
        "correct": 5,
        "total": 14,
        "notes": "Lote com latencias ainda muito altas; OCR/ORB ainda demasiado caros e pouca exclusao.",
        "rows": [
            ["3b4d8bf5", "rejected", "Great Furnace", 70.4, 49.4, 25811, "score_below_minimum"],
            ["8013ed1b", "ambiguous", "Swamp", 65.4, 33.5, 29415, "top_candidates_too_close"],
            ["45740786", "ambiguous", "Fatespinner", 72.5, 39.4, 25694, "top_candidates_too_close"],
            ["f24425f3", "ambiguous", "Taj-Nar Swordsmith", 70.5, 42, 24327, "top_candidates_too_close"],
            ["6f579a27", "rejected", "Sculpting Steel", 73.6, 57.6, 24768, "score_below_minimum"],
            ["6e944890", "ambiguous", "Island", 76, 37.3, 25171, "top_candidates_too_close"],
            ["1c93e096", "ambiguous", "Goblin Charbelcher", 72.2, 40.9, 25398, "top_candidates_too_close"],
            ["fc95e17c", "ambiguous", "Tower of Eons", 72.2, 43.9, 24758, "top_candidates_too_close"],
            ["a6c5f19d", "ambiguous", "Cloudpost", 72.5, 72.5, 25191, "score_requires_confirmation"],
            ["78259251", "ambiguous", "Razor Barrier", 74.4, 38.4, 24486, "top_candidates_too_close"],
            ["7f6f5221", "ambiguous", "Blinkmoth Urn", 78.7, 44.9, 25693, "top_candidates_too_close"],
            ["4469cbcf", "ambiguous", "Groffskithur", 74.9, 40.4, 25359, "top_candidates_too_close"],
            ["1d0bb31a", "ambiguous", "Sculpting Steel", 71.6, 44.7, 25486, "top_candidates_too_close"],
            ["60c8f750", "rejected", "-", 47.3, 0, 199, "quality_gate_rejected"],
        ],
    },
    "B2_orb_batch": {
        "label": "ORB batch e reducao de chamadas",
        "reported_accuracy": "7/12",
        "correct": 7,
        "total": 12,
        "notes": "Latencia melhorou muito apos batch ORB, mas todas continuavam ambiguas.",
        "rows": [
            ["4021c900", "ambiguous", "Goblin Charbelcher", 70.6, 40.8, 3882, "top_candidates_too_close"],
            ["62f89384", "ambiguous", "Sculpting Steel", 68.3, 41, 4172, "top_candidates_too_close"],
            ["c4750822", "ambiguous", "Goblin Charbelcher", 72.6, 42.4, 6202, "top_candidates_too_close"],
            ["468d8225", "ambiguous", "Glimmervoid", 69.1, 36.9, 4803, "top_candidates_too_close"],
            ["de1facbe", "ambiguous", "Silver Myr", 70.6, 47, 6326, "top_candidates_too_close"],
            ["2c011971", "ambiguous", "Clockwork Dragon", 71.3, 40.1, 3860, "top_candidates_too_close"],
            ["c1aee4b1", "ambiguous", "Lightning Greaves", 78.8, 43.7, 5202, "top_candidates_too_close"],
            ["8850ed55", "ambiguous", "Blinkmoth Urn", 71.8, 47.1, 3918, "top_candidates_too_close"],
            ["a024220b", "ambiguous", "Thoughtcast", 74.8, 45.8, 4173, "top_candidates_too_close"],
            ["ab5a3773", "ambiguous", "Lumengrid Sentinel", 71.3, 40.8, 4076, "top_candidates_too_close"],
            ["ab9f0ef8", "ambiguous", "Sculpting Steel", 71.4, 40.9, 6917, "top_candidates_too_close"],
            ["16120eb1", "rejected", "Great Furnace", 70.9, 52.6, 6017, "score_below_minimum"],
            ["8ff99310", "ambiguous", "Steel Wall", 71.9, 42.3, 5430, "top_candidates_too_close"],
            ["26e009c8", "rejected", "Great Furnace", 73.3, 63, 6800, "score_below_minimum"],
            ["fe01f949", "ambiguous", "Sun Droplet", 66.8, 39.5, 4993, "top_candidates_too_close"],
            ["7760b5d5", "ambiguous", "Sculpting Steel", 63.9, 41.7, 4050, "top_candidates_too_close"],
            ["b4141f85", "ambiguous", "Ornithopter", 80, 42.3, 4032, "top_candidates_too_close"],
            ["577a3a32", "ambiguous", "Ornithopter", 74, 42.9, 4275, "top_candidates_too_close"],
        ],
    },
    "B3_auto_scan_lock": {
        "label": "Scan automatico e camera mais acima",
        "reported_accuracy": "5/12",
        "correct": 5,
        "total": 12,
        "notes": "Foi removido o hero e automatizado o fluxo mobile; lock ainda lento e nem sempre focava a carta.",
        "rows": [
            ["fcfcc410", "rejected", "Great Furnace", 70.2, 58.7, 4165, "score_below_minimum"],
            ["ad446885", "ambiguous", "Blinding Beam", 78.7, 37.7, 5127, "top_candidates_too_close"],
            ["fa653867", "ambiguous", "Bottle Gnomes", 80.6, 40.9, 4030, "top_candidates_too_close"],
            ["c7b21215", "ambiguous", "Lightning Greaves", 73.7, 46.4, 4028, "top_candidates_too_close"],
            ["7212b2e9", "ambiguous", "Incite War", 75.8, 33.9, 4168, "top_candidates_too_close"],
            ["d22572e8", "ambiguous", "Silver Myr", 79, 41.3, 4139, "top_candidates_too_close"],
            ["48846748", "ambiguous", "Sculpting Steel", 74.1, 49.5, 4097, "top_candidates_too_close"],
            ["70d73e49", "ambiguous", "Fireshrieker", 79.3, 34.5, 3947, "top_candidates_too_close"],
            ["cbfd5140", "rejected", "Cloudpost", 78.4, 55.8, 4878, "score_below_minimum"],
            ["5d6da963", "ambiguous", "Override", 75.8, 37, 4360, "top_candidates_too_close"],
            ["c18f736a", "ambiguous", "Island", 77.5, 42.4, 4252, "top_candidates_too_close"],
            ["f0fa5ddf", "rejected", "Pentavus", 71.3, 51, 4076, "score_below_minimum"],
        ],
    },
    "B4_progressive_exclusion": {
        "label": "Exclusao progressiva explicita",
        "reported_accuracy": "6/12",
        "correct": 6,
        "total": 12,
        "notes": "Top 3 passou a ser auditavel; fase util para perceber pesos e candidatos, embora com regressao temporaria.",
        "rows": [
            ["41f335da", "ambiguous", "Tower of Eons", 71.7, 44.1, 2993, "top_candidates_too_close"],
            ["e113c643", "ambiguous", "Great Furnace", 68, 41.1, 2722, "top_candidates_too_close"],
            ["ee2e5a93", "ambiguous", "Thought Prison", 79.6, 39.8, 2953, "top_candidates_too_close"],
            ["ae078c54", "ambiguous", "Dream's Grip", 69.6, 35.5, 2645, "top_candidates_too_close"],
            ["ae1c9053", "ambiguous", "Sculpting Steel", 70.4, 46.7, 2895, "top_candidates_too_close"],
            ["8e739f9c", "ambiguous", "Pentavus", 69.5, 45.7, 2883, "top_candidates_too_close"],
            ["220a354e", "ambiguous", "Blinkmoth Urn", 70.4, 43.3, 4736, "top_candidates_too_close"],
            ["da42a2b7", "ambiguous", "Taj-Nar Swordsmith", 69.3, 40, 3395, "top_candidates_too_close"],
            ["6f9d4ede", "ambiguous", "Confusion in the Ranks", 72.4, 38.4, 3153, "top_candidates_too_close"],
            ["2ef1390f", "ambiguous", "Neurok Familiar", 71.5, 35.7, 3347, "top_candidates_too_close"],
            ["81c03362", "ambiguous", "Cloudpost", 74.8, 41.3, 2994, "top_candidates_too_close"],
            ["36d3ea5e", "ambiguous", "Goblin Charbelcher", 70.7, 52.5, 2921, "score_requires_confirmation"],
        ],
    },
    "B5_scoring_consensus": {
        "label": "Recalibracao scoring e consenso",
        "reported_accuracy": "11/15",
        "correct": 11,
        "total": 15,
        "notes": "Ultimo lote reportado; mediana 2883ms; confirma melhoria com amostra maior.",
        "rows": [
            ["66a20812", "ambiguous", "Solemn Simulacrum", 69.3, 43.5, 2751, "top_candidates_too_close"],
            ["1453e130", "ambiguous", "Tower of Eons", 70.4, 70.1, 2816, "score_requires_confirmation"],
            ["bea1d7ce", "ambiguous", "Chrome Mox", 70, 53.1, 3193, "top_candidates_too_close"],
            ["8490b65d", "ambiguous", "Lightning Greaves", 70.8, 63.6, 3214, "top_candidates_too_close"],
            ["e8b761e0", "ambiguous", "Silver Myr", 68.5, 53.4, 3120, "top_candidates_too_close"],
            ["ac0e80a2", "ambiguous", "Cloudpost", 70.8, 70.9, 3053, "top_candidates_too_close"],
            ["28dc9bbb", "ambiguous", "Sculpting Steel", 66.5, 59.2, 3399, "score_requires_confirmation"],
            ["305f6269", "ambiguous", "Hum of the Radix", 69.4, 61.4, 2879, "score_requires_confirmation"],
            ["99d7fb6f", "ambiguous", "Sculpting Steel", 71.4, 79.2, 3355, "score_requires_confirmation"],
            ["dfdf6f36", "ambiguous", "Blinkmoth Urn", 72, 75.4, 2785, "score_requires_confirmation"],
            ["fb907f80", "ambiguous", "Clockwork Condor", 71.4, 56.3, 2926, "top_candidates_too_close"],
            ["786e9cf1", "ambiguous", "Clockwork Dragon", 69.1, 57.5, 2683, "top_candidates_too_close"],
            ["e5e1d30c", "ambiguous", "Pearl Shard", 71.8, 59.1, 2883, "top_candidates_too_close"],
            ["6091709f", "ambiguous", "Great Furnace", 70.7, 70.2, 2723, "score_requires_confirmation"],
            ["753d323a", "ambiguous", "Slith Predator", 71.7, 53.4, 2700, "top_candidates_too_close"],
        ],
    },
}


TIMINGS = [
    ["B4_progressive_exclusion", "Prepare", 2],
    ["B4_progressive_exclusion", "Perspective", 233.8],
    ["B4_progressive_exclusion", "Hash gen", 227.5],
    ["B4_progressive_exclusion", "Hash search", 907.2],
    ["B4_progressive_exclusion", "OCR", 172.4],
    ["B4_progressive_exclusion", "ORB", 1271.8],
    ["B4_progressive_exclusion", "Scoring", 3.1],
    ["B4_progressive_exclusion", "DB", 28.6],
    ["B4_progressive_exclusion", "Median", 2953],
    ["B5_scoring_consensus", "Prepare", 1.5],
    ["B5_scoring_consensus", "Perspective", 193.2],
    ["B5_scoring_consensus", "Hash gen", 236.9],
    ["B5_scoring_consensus", "Hash search", 785.1],
    ["B5_scoring_consensus", "OCR", 142.9],
    ["B5_scoring_consensus", "ORB", 1340.9],
    ["B5_scoring_consensus", "Scoring", 3.4],
    ["B5_scoring_consensus", "DB", 30.9],
    ["B5_scoring_consensus", "Median", 2883],
]


CHANGES = [
    ["B0 -> B1", "Criacao da pipeline v2 mensuravel", "Foram adicionados scans persistentes, candidatos, timings, dashboard e logs auditaveis.", "Passamos a medir qualidade, score, decisao, tempos e motivos de rejeicao."],
    ["B1 -> B2", "ORB em batch", "O comparador ORB deixou de chamar o microservico candidato a candidato e passou a agrupar comparacoes.", "Latencia caiu de cerca de 25s para cerca de 5s."],
    ["B2 -> B3", "Camera/scan automatico e UI mobile", "Removido hero da pagina de scan, camera subida, auto start e tentativa de lock antes de enviar.", "Menos scroll e menos intervencao no telefone; qualidade de captura ficou mais controlada."],
    ["B3 -> B4", "Exclusao progressiva explicita", "Introduzidos cortes por fases: hash, marker, verification e final stage; Top 3 com scores por comparador.", "Melhor auditabilidade tecnica e cientifica; regressao temporaria ajudou a calibrar pesos."],
    ["B4 -> B5", "Recalibracao de score e consenso", "Ajuste de pesos pHash/edge/color, ORB fraco deixou de penalizar, boost por consenso e decisao por score+margem.", "Accuracy reportada subiu para 11/15 mantendo mediana perto de 2.9s."],
    ["B5 -> Proxima fase", "Normalizacao MTG e ORB condicional", "Detecao de ratio MTG, quatro cantos, perspective correction vertical 672x936 e ORB apenas quando necessario.", "Pendente de novo benchmark online; objetivo e melhorar accuracy e reduzir custo ORB em scans faceis."],
]


def build_rows():
    iteration_rows = [["Iteracao", "Label", "Scans", "Corretas", "Accuracy %", "Latencia media ms", "Latencia mediana ms", "Latencia min ms", "Latencia max ms", "Accuracy reportada", "Notas"]]
    scan_rows = [["Iteracao", "Scan", "Status", "Produto top1", "Quality", "Score", "Latency ms", "Reason"]]

    for key, batch in SCAN_BATCHES.items():
        rows = batch["rows"]
        latencies = [r[5] for r in rows]
        accuracy = None
        if batch["correct"] is not None and batch["total"]:
            accuracy = round((batch["correct"] / batch["total"]) * 100, 1)
        iteration_rows.append([
            key,
            batch["label"],
            len(rows) if rows else batch["total"],
            batch["correct"],
            accuracy,
            avg(latencies) if latencies else None,
            median(latencies) if latencies else None,
            min(latencies) if latencies else None,
            max(latencies) if latencies else None,
            batch["reported_accuracy"],
            batch["notes"],
        ])
        for row in rows:
            scan_rows.append([key, *row])

    timing_rows = [["Iteracao", "Componente", "Tempo ms"], *TIMINGS]
    change_rows = [["Iteracao", "Alteracao", "O que foi feito", "Impacto observado"], *CHANGES]
    chart_rows = [["Iteracao", "Latencia mediana ms", "Accuracy %"]]
    for row in iteration_rows[1:]:
        chart_rows.append([row[0], row[6], row[4]])

    return iteration_rows, scan_rows, timing_rows, change_rows, chart_rows


def write_xlsx(path: Path):
    sheets = [
        ("Resumo", build_rows()[0]),
        ("Scans_Detalhados", build_rows()[1]),
        ("Tempos_Componentes", build_rows()[2]),
        ("Alteracoes", build_rows()[3]),
        ("Dados_Grafico", build_rows()[4]),
    ]
    with zipfile.ZipFile(path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        overrides = "\n".join(
            f'<Override PartName="/xl/worksheets/sheet{i}.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            for i in range(1, len(sheets) + 1)
        )
        zf.writestr("[Content_Types].xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
{overrides}
</Types>""")
        zf.writestr("_rels/.rels", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>""")
        sheet_defs = "\n".join(
            f'<sheet name="{name}" sheetId="{i}" r:id="rId{i}"/>'
            for i, (name, _) in enumerate(sheets, start=1)
        )
        zf.writestr("xl/workbook.xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets>
{sheet_defs}
</sheets>
</workbook>""")
        rels = "\n".join(
            f'<Relationship Id="rId{i}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet{i}.xml"/>'
            for i in range(1, len(sheets) + 1)
        )
        zf.writestr("xl/_rels/workbook.xml.rels", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
{rels}
</Relationships>""")
        for i, (_, rows) in enumerate(sheets, start=1):
            zf.writestr(f"xl/worksheets/sheet{i}.xml", sheet_xml(rows))


def main():
    path = ROOT / "webcatalogue_recognition_chat_detailed_data.xlsx"
    write_xlsx(path)
    print("Generated", path)


if __name__ == "__main__":
    main()

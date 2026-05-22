from __future__ import annotations

import html
import math
import textwrap
import zipfile
from datetime import date
from pathlib import Path
from xml.sax.saxutils import escape


ROOT = Path(__file__).resolve().parent
REPORT_DATE = date(2026, 5, 22).isoformat()


def avg(values: list[float]) -> float:
    return sum(values) / len(values) if values else 0.0


def median(values: list[float]) -> float:
    ordered = sorted(values)
    if not ordered:
        return 0.0
    middle = len(ordered) // 2
    if len(ordered) % 2:
        return ordered[middle]
    return (ordered[middle - 1] + ordered[middle]) / 2


PHASES = [
    {
        "phase": "P0",
        "label": "Baseline inicial",
        "date": "2026-05-21",
        "main_changes": "Pipeline visual ainda ampla; ORB/comparacao pesada por candidato; crop/perspetiva genericos.",
        "conditions": "Cartas MTG em set reduzido; scans mobile; catalogo de teste; sem exclusao progressiva explicita.",
        "latency_ms": 25000,
        "latency_source": "Valor observado/reportado: cerca de 25s.",
        "correct": 3.5,
        "total": 12,
        "accuracy_pct": 29.2,
        "user_value": "Resposta demasiado lenta; baixa confianca; pouca utilidade operacional.",
    },
    {
        "phase": "P1",
        "label": "ORB batch",
        "date": "2026-05-21",
        "main_changes": "ORB deixou de chamar o microservico candidato a candidato; comparacao batch para markers.",
        "conditions": "Mesmo tipo de scans; dataset/fingerprints existentes; decisao ainda conservadora.",
        "latency_ms": avg([3882, 4172, 6202, 4803, 6326, 3860, 5202, 3918, 4173, 4076, 6917, 6017, 5430, 6800, 4993, 4050, 4032, 4275]),
        "latency_source": "Media calculada a partir da tabela reportada apos batch ORB.",
        "correct": 7,
        "total": 12,
        "accuracy_pct": 58.3,
        "user_value": "Reducao drastica de espera; ainda com muitas respostas ambiguas.",
    },
    {
        "phase": "P2",
        "label": "Card lock e limites de candidatos",
        "date": "2026-05-21",
        "main_changes": "Lock visual antes do scan; crop bloqueado; reducao de pools: verification, marker e scored candidates.",
        "conditions": "Mobile; scan automatico; tentativa de garantir carta centrada antes de capturar.",
        "latency_ms": avg([4815, 4096, 4430, 4141, 4195, 4010, 4183, 4955, 4619, 4493, 4526, 4248, 3909, 4017, 4120, 4693, 3870, 3911, 4235, 3864]),
        "latency_source": "Media calculada a partir do lote com lock/limites.",
        "correct": 8,
        "total": 12,
        "accuracy_pct": 66.7,
        "user_value": "Menos scroll/intervencao; captura mais controlada; tempo ainda perto de 4s.",
    },
    {
        "phase": "P3",
        "label": "Exclusao progressiva explicita",
        "date": "2026-05-22",
        "main_changes": "Fases hash, marker, verification e final stage com contadores e cortes reais.",
        "conditions": "Top 3 visivel na dashboard; ORB ainda dominante em alguns casos; score mais auditavel.",
        "latency_ms": 2946,
        "latency_source": "Mediana reportada apos exclusao progressiva.",
        "correct": 6,
        "total": 12,
        "accuracy_pct": 50.0,
        "user_value": "Melhor explicabilidade; regressao temporaria de accuracy usada para calibrar pesos e score.",
    },
    {
        "phase": "P4",
        "label": "Recalibracao scoring e consenso",
        "date": "2026-05-22",
        "main_changes": "Mais peso para pHash/edge; ORB fraco deixa de penalizar; boost por consenso; decisao por score+margem.",
        "conditions": "Dashboard mostra Top 3, scores por comparador e margem Delta.",
        "latency_ms": median([2751, 2816, 3193, 3214, 3120, 3053, 3399, 2879, 3355, 2785, 2926, 2683, 2883, 2723, 2700]),
        "latency_source": "Mediana calculada a partir do ultimo lote detalhado de 15 scans.",
        "correct": 11,
        "total": 15,
        "accuracy_pct": 73.3,
        "user_value": "Resposta em cerca de 3s; accuracy consolidada reportada 11/15; melhor base para prova de conceito.",
    },
    {
        "phase": "P5",
        "label": "Normalizacao MTG e ORB condicional",
        "date": "2026-05-22",
        "main_changes": "OpenCV mtg_card com ratio/cantos/perspetiva 672x936; ORB apenas quando hash/edge/color nao dao margem forte.",
        "conditions": "Implementado; requer redeploy/restart do microservico OpenCV antes de benchmark final.",
        "latency_ms": 2200,
        "latency_source": "Meta/estimativa tecnica pos-implementacao; pendente de benchmark online.",
        "correct": None,
        "total": None,
        "accuracy_pct": None,
        "user_value": "Melhor consistencia esperada; menor custo ORB em scans faceis.",
    },
]


DETAILS = [
    ["P0", "Latencia", "cerca de 25s", "ORB/comparacao pesada ainda demasiado ampla."],
    ["P1", "Latencia", "queda para ~5s", "Batch ORB removeu chamadas repetidas ao microservico."],
    ["P2", "Usabilidade", "camera mais automatica + lock", "Menos scroll e menos scans disparados cedo demais."],
    ["P3", "Ciencia/explicabilidade", "contadores por fase", "Permite justificar exclusao progressiva na tese."],
    ["P4", "Accuracy", "11/15 reportado", "Score passa a refletir consenso entre comparadores e confirma a melhoria num lote maior."],
    ["P5", "Normalizacao", "mtg_card", "Perspetiva standard para reduzir variacao entre scans."],
]


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


def write_xlsx(path: Path):
    summary_rows = [
        ["Fase", "Etiqueta", "Data", "Latencia ms", "Corretas", "Total", "Accuracy %", "Alteracoes", "Condicoes", "Valor para o utilizador", "Fonte latencia"],
    ]
    for phase in PHASES:
        summary_rows.append([
            phase["phase"],
            phase["label"],
            phase["date"],
            round(phase["latency_ms"], 1) if phase["latency_ms"] is not None else None,
            phase["correct"],
            phase["total"],
            phase["accuracy_pct"],
            phase["main_changes"],
            phase["conditions"],
            phase["user_value"],
            phase["latency_source"],
        ])

    chart_rows = [["Fase", "Latencia ms", "Accuracy %", "Indice valor utilizador"]]
    baseline_latency = PHASES[0]["latency_ms"]
    for phase in PHASES:
        accuracy = phase["accuracy_pct"]
        user_value = None
        if accuracy is not None:
            latency_gain = max(0, min(100, (1 - (phase["latency_ms"] / baseline_latency)) * 100))
            user_value = round((latency_gain * 0.45) + (accuracy * 0.55), 1)
        chart_rows.append([
            f'{phase["phase"]} - {phase["label"]}',
            round(phase["latency_ms"], 1) if phase["latency_ms"] is not None else None,
            accuracy,
            user_value,
        ])

    detail_rows = [["Fase", "Dimensao", "Resultado observado", "Interpretacao"], *DETAILS]

    with zipfile.ZipFile(path, "w", compression=zipfile.ZIP_DEFLATED) as zf:
        zf.writestr("[Content_Types].xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>""")
        zf.writestr("_rels/.rels", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>""")
        zf.writestr("xl/workbook.xml", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets>
<sheet name="Evolucao" sheetId="1" r:id="rId1"/>
<sheet name="Dados_Grafico" sheetId="2" r:id="rId2"/>
<sheet name="Notas" sheetId="3" r:id="rId3"/>
</sheets>
</workbook>""")
        zf.writestr("xl/_rels/workbook.xml.rels", """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
</Relationships>""")
        zf.writestr("xl/worksheets/sheet1.xml", sheet_xml(summary_rows))
        zf.writestr("xl/worksheets/sheet2.xml", sheet_xml(chart_rows))
        zf.writestr("xl/worksheets/sheet3.xml", sheet_xml(detail_rows))


def report_markdown() -> str:
    baseline = PHASES[0]
    current = PHASES[4]
    latency_reduction = (1 - (current["latency_ms"] / baseline["latency_ms"])) * 100
    accuracy_gain = current["accuracy_pct"] - baseline["accuracy_pct"]
    lines = [
        "# WebCatalogue Visual Recognition - Relatorio de Evolucao",
        "",
        f"Data: {REPORT_DATE}",
        "",
        "## Resumo executivo",
        "",
        "A pipeline de reconhecimento visual do WebCatalogue evoluiu de um matcher exploratorio para uma pipeline mensuravel e auditavel para reconhecimento de cartas MTG. Os primeiros testes tinham uma latencia aproximada de 25 segundos e uma accuracy na ordem de 3-4 acertos em 12 scans. O benchmark consolidado mais recente reporta 11 acertos em 15 scans, com mediana de latencia perto de 3 segundos.",
        "",
        f"Em termos quantitativos, a latencia caiu cerca de {latency_reduction:.1f}% e a accuracy subiu {accuracy_gain:.1f} pontos percentuais entre o baseline e o ultimo lote consolidado. Isto representa uma melhoria direta para o utilizador: menos espera, mais confianca e menor necessidade de repetir o scan.",
        "",
        "Esta evolucao e relevante academicamente porque cada melhoria foi motivada por um gargalo ou modo de falha observado: chamadas ORB excessivas, captura/crop instavel, pools de candidatos demasiado amplas, compressao de score e normalizacao de perspetiva insuficiente.",
        "",
        "## Metodologia",
        "",
        "Os valores foram obtidos a partir dos lotes de teste reportados durante a evolucao do modulo. Quando existia lista de scans, foi calculada media ou mediana sobre os tempos apresentados. A fase P5 representa uma alteracao ja preparada/implementada, mas ainda pendente de novo benchmark online apos redeploy do microservico OpenCV.",
        "",
        "## Tabela de evolucao",
        "",
        "| Fase | Alteracao principal | Latencia | Accuracy | Interpretacao |",
        "|---|---|---:|---:|---|",
    ]
    for p in PHASES:
        acc = "-" if p["accuracy_pct"] is None else f'{p["accuracy_pct"]:.1f}%'
        lines.append(f'| {p["phase"]} {p["label"]} | {p["main_changes"]} | {p["latency_ms"]:.1f} ms | {acc} | {p["user_value"]} |')
    lines += [
        "",
        "## Relevancia cientifica",
        "",
        "O historico de benchmarks fornece evidencia de um metodo iterativo de engenharia: medir, identificar gargalos, alterar uma parte do sistema e voltar a medir. A reducao de latencia valida a estrategia de exclusao progressiva. A melhoria de accuracy valida a passagem de matching visual generico para reconhecimento orientado ao dominio das cartas.",
        "",
        "## Principais aprendizagens",
        "",
        "- ORB e util como sinal de confianca, mas e demasiado caro e ruidoso para correr contra pools amplas.",
        "- pHash e edge/hash sao bons sinais iniciais para ranking de cartas MTG quando o crop e estavel.",
        "- Um Top 3 visivel com scores por comparador e essencial para calibracao e auditabilidade cientifica.",
        "- A normalizacao de perspetiva especifica para cartas e a proxima alavanca principal de accuracy.",
        "",
        "## Proximo benchmark",
        "",
        "Depois de redeploy/restart do microservico OpenCV com normalizacao `mtg_card`, deve ser executado um novo benchmark controlado com pelo menos 3 repeticoes por cenario: luz ideal, luz natural, pouca luz, glare, sleeve, carta inclinada e motion blur.",
        "",
        "O Excel gerado em paralelo contem uma folha de evolucao completa, uma folha simplificada para graficos e uma folha de notas interpretativas. Para apresentacao, recomenda-se um grafico combinado com latencia em linha descendente e accuracy em linha ascendente.",
    ]
    return "\n".join(lines) + "\n"


def report_html(markdown_text: str) -> str:
    rows = []
    for p in PHASES:
        acc = "-" if p["accuracy_pct"] is None else f'{p["accuracy_pct"]:.1f}%'
        rows.append(
            "<tr>"
            f"<td>{html.escape(p['phase'])}</td>"
            f"<td>{html.escape(p['label'])}</td>"
            f"<td>{p['latency_ms']:.1f}</td>"
            f"<td>{acc}</td>"
            f"<td>{html.escape(p['main_changes'])}</td>"
            f"<td>{html.escape(p['conditions'])}</td>"
            "</tr>"
        )
    return f"""<!doctype html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>WebCatalogue Visual Recognition - Relatorio de Evolucao</title>
<style>
body{{font-family:Arial,sans-serif;margin:42px;color:#1f2937;line-height:1.5}}
h1,h2{{color:#111827}} table{{border-collapse:collapse;width:100%;font-size:13px}} th,td{{border:1px solid #d1d5db;padding:8px;vertical-align:top}} th{{background:#f3f4f6}} .kpi{{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:18px 0}} .card{{border:1px solid #d1d5db;padding:12px;background:#f9fafb}} .big{{font-size:26px;font-weight:700}}
</style>
</head>
<body>
<h1>WebCatalogue Visual Recognition - Relatorio de Evolucao</h1>
<p><strong>Data:</strong> {REPORT_DATE}</p>
<div class="kpi">
<div class="card"><div>Latencia</div><div class="big">25s -> ~3s</div></div>
<div class="card"><div>Accuracy</div><div class="big">3-4/12 -> 11/15</div></div>
<div class="card"><div>Valor para o utilizador</div><div class="big">Mais rapido + mais correto</div></div>
</div>
<h2>Resumo executivo</h2>
<p>A pipeline evoluiu de um matcher exploratorio para uma pipeline mensuravel e auditavel. As experiencias mostram uma reducao significativa de latencia e uma melhoria relevante de accuracy, suportando o caminho de exclusao progressiva, quality gates, card lock, calibracao de score e normalizacao especifica para MTG.</p>
<p><strong>Nota metodologica:</strong> P0 a P4 usam medicoes reportadas nos testes. P5 esta marcada como fase preparada e deve ser validada em novo benchmark apos redeploy/restart do microservico OpenCV.</p>
<h2>Dados de evolucao</h2>
<table><thead><tr><th>Fase</th><th>Etiqueta</th><th>Latencia ms</th><th>Accuracy</th><th>Alteracoes</th><th>Condicoes</th></tr></thead><tbody>{''.join(rows)}</tbody></table>
<h2>Interpretacao academica</h2>
<p>Estes resultados demonstram um metodo cientifico iterativo: cada gargalo foi medido, uma alteracao direcionada foi introduzida e o efeito foi registado. Os dados suportam a transicao de matching visual generico para uma pipeline de reconhecimento visual orientada ao dominio.</p>
</body>
</html>"""


def pdf_escape(text: str) -> str:
    return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def write_pdf(path: Path, text: str):
    pages = []
    logical_lines = []
    for line in text.splitlines():
        if not line.strip():
            logical_lines.append("")
            continue
        logical_lines.extend(textwrap.wrap(line, width=92) or [""])

    for i in range(0, len(logical_lines), 42):
        pages.append(logical_lines[i:i + 42])

    objects = []
    objects.append("<< /Type /Catalog /Pages 2 0 R >>")
    kids = " ".join(f"{3 + i * 2} 0 R" for i in range(len(pages)))
    objects.append(f"<< /Type /Pages /Kids [{kids}] /Count {len(pages)} >>")

    for index, page_lines in enumerate(pages):
        page_obj = 3 + index * 2
        content_obj = page_obj + 1
        objects.append(f"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /Contents {content_obj} 0 R >>")
        stream_lines = ["BT", "/F1 10 Tf", "50 750 Td", "14 TL"]
        for line in page_lines:
            stream_lines.append(f"({pdf_escape(line)}) Tj")
            stream_lines.append("T*")
        stream_lines.append("ET")
        stream = "\n".join(stream_lines)
        objects.append(f"<< /Length {len(stream.encode('latin-1', errors='replace'))} >>\nstream\n{stream}\nendstream")

    pdf = "%PDF-1.4\n"
    offsets = [0]
    for number, obj in enumerate(objects, start=1):
        offsets.append(len(pdf.encode("latin-1")))
        pdf += f"{number} 0 obj\n{obj}\nendobj\n"
    xref_offset = len(pdf.encode("latin-1"))
    pdf += f"xref\n0 {len(objects) + 1}\n0000000000 65535 f \n"
    for offset in offsets[1:]:
        pdf += f"{offset:010d} 00000 n \n"
    pdf += f"trailer\n<< /Size {len(objects) + 1} /Root 1 0 R >>\nstartxref\n{xref_offset}\n%%EOF"
    path.write_bytes(pdf.encode("latin-1", errors="replace"))


def main():
    ROOT.mkdir(parents=True, exist_ok=True)
    markdown = report_markdown()
    (ROOT / "webcatalogue_recognition_evolution_report.md").write_text(markdown, encoding="utf-8")
    (ROOT / "webcatalogue_recognition_evolution_report.html").write_text(report_html(markdown), encoding="utf-8")
    write_xlsx(ROOT / "webcatalogue_recognition_evolution_data.xlsx")
    write_pdf(ROOT / "webcatalogue_recognition_evolution_report.pdf", markdown)
    print("Generated recognition evolution report files in", ROOT)


if __name__ == "__main__":
    main()

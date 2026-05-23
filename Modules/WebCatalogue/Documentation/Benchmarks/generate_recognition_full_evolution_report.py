from __future__ import annotations

import json
import html
import statistics
import textwrap
import zipfile
from datetime import date
from pathlib import Path
from xml.sax.saxutils import escape

from generate_recognition_detailed_chat_data import SCAN_BATCHES, TIMINGS


ROOT = Path(__file__).resolve().parent
WORKSPACE = ROOT.parents[3]
DOWNLOADS = Path(r"c:\Users\WL_av\Downloads")
TODAY = date.today().isoformat()


AB_TESTS = [
    {
        "label": "409 -> 410",
        "before": "409",
        "after": "410",
        "change": "Comparacao antes/depois de ajuste de leitura regional/structured regions.",
        "interpretation": "Usado para validar se a alteracao melhorava a posicao do candidato real e a margem.",
    },
    {
        "label": "412 -> 413",
        "before": "412",
        "after": "413",
        "change": "Reducao/agressividade do funil final de candidatos.",
        "interpretation": "Regressao: o candidato correto saiu do top 5. Originou protecao de short_hash no pool final.",
    },
    {
        "label": "414 -> 415",
        "before": "414",
        "after": "415",
        "change": "Teste apos ajustes de protecao/ranking, mas com captura pior.",
        "interpretation": "Regressao causada por captura/crop invalido: OpenCV confidence 0 e contour null. Originou rejeicao de normalizacao sem contorno.",
    },
    {
        "label": "417 -> 418",
        "before": "417",
        "after": "418",
        "change": "Full-card visibility gate + rejeicao de normalizacao OpenCV sem contorno.",
        "interpretation": "Melhoria clara: o mesmo produto passou de missed_top_5 para top_1_match.",
    },
]


IMPLEMENTATION_TIMELINE = [
    ["T0", "Baseline conceptual", "Matcher visual amplo, comparacao pesada e pouca exclusao.", "3-4/12; cerca de 25s", "Mostrou que acertar carta sem pipeline orientada ao dominio era lento e pouco fiavel."],
    ["T1", "Pipeline auditavel", "Criadas tabelas/logs de scans, scores, timings, candidatos e dashboard.", "Passou a existir evidencia por scan", "Tornou a solucao mensuravel, essencial para tese."],
    ["T2", "Pre-processamento/fingerprints", "Hash, edge, color, embedding, markers e perfis persistidos para evitar recalculo normal.", "Latencia desceu fortemente quando combinado com batch ORB", "Confirma a estrategia de pre-processamento de catalogo."],
    ["T3", "ORB batch", "ORB deixou de executar chamada por candidato e passou a comparar por lote/top candidates.", "~25s para ~5s", "Primeira grande prova de melhoria operacional."],
    ["T4", "Scan automatico mobile", "Camera mais acima, hero removido, auto scan e tentativa de focus/card lock.", "UX melhor, mas lock ainda podia aceitar frames maus", "Mostrou que qualidade de captura e tao importante como o matcher."],
    ["T5", "Exclusao progressiva", "Scope, quality, hash, marker, verification e final stage com limites e contadores.", "~3s, mas regressao temporaria de accuracy", "A regressao foi util porque expôs onde candidatos corretos eram eliminados."],
    ["T6", "Top 3/Top 5 e ZIP diagnostico", "Export CSV, ground truth, classificacao top1/top3/top5/missed e ZIP com imagens.", "Permitiu A/B real entre sessoes 402-418", "Ferramenta cientifica chave: comparar input, candidatos e real."],
    ["T7", "Short-hash protected lane", "Reserva de candidatos short_hash no final stage e ranking conservador para bons short hashes.", "412->413 revelou regressao; alteracao protege candidatos certos", "Evita que verification_pool domine e elimine sinais fortes."],
    ["T8", "Quality/crop gate", "Rejeicao de normalizacao OpenCV sem contorno/confianca e lock exige carta inteira visivel.", "417->418: missed_top_5 para top_1_match", "Mostra que uma captura melhor melhora accuracy mais que apenas mexer em pesos."],
    ["T9", "Visualizacao 3D/VR", "Carta procedural com frente/verso MTG, foil suave, VR fallback e environment Mirrodin.", "Fora do benchmark de leitura", "Demonstra caminho de produto imersivo apos reconhecimento."],
]


def avg(values: list[float]) -> float | None:
    return round(sum(values) / len(values), 1) if values else None


def median(values: list[float]) -> float | None:
    return round(statistics.median(values), 1) if values else None


def pct(correct, total):
    if correct is None or not total:
        return None
    return round((float(correct) / float(total)) * 100, 1)


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
    if isinstance(value, bool):
        return f'<c r="{ref}" t="inlineStr"><is><t>{"TRUE" if value else "FALSE"}</t></is></c>'
    if isinstance(value, (int, float)):
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


def safe_get(obj, *path, default=None):
    current = obj
    for key in path:
        if current is None:
            return default
        if isinstance(current, dict):
            current = current.get(key)
        elif isinstance(current, list) and isinstance(key, int):
            current = current[key] if len(current) > key else None
        else:
            return default
    return default if current is None else current


def session_zip_path(session_id: str) -> Path:
    return DOWNLOADS / f"webcatalogue-session-{session_id}-diagnostic.zip"


def read_manifest(session_id: str) -> dict | None:
    path = session_zip_path(session_id)
    if not path.exists():
        return None
    with zipfile.ZipFile(path) as zf:
        with zf.open("manifest.json") as fh:
            data = json.loads(fh.read().decode("utf-8"))
    data["_zip_name"] = path.name
    data["_zip_size"] = path.stat().st_size
    data["_zip_mtime"] = path.stat().st_mtime
    return data


def all_zip_manifests() -> list[dict]:
    manifests = []
    for path in sorted(DOWNLOADS.glob("webcatalogue-session-*-diagnostic.zip")):
        session_id = path.stem.replace("webcatalogue-session-", "").replace("-diagnostic", "")
        manifest = read_manifest(session_id)
        if manifest:
            manifests.append(manifest)
    return manifests


def product_label(product: dict | None) -> str:
    if not product:
        return "-"
    reference = product.get("reference") or ""
    name = product.get("name") or ""
    return f"{reference} {name}".strip() or "-"


def candidate_label(candidate: dict | None) -> str:
    if not candidate:
        return "-"
    reference = candidate.get("reference") or ""
    name = candidate.get("name") or ""
    return f"{reference} {name}".strip() or "-"


def zip_session_row(manifest: dict) -> list:
    session = manifest.get("session", {})
    metadata = session.get("metadata", {}) or {}
    capture = safe_get(manifest, "captures", 0, default={}) or {}
    capture_meta = capture.get("metadata", {}) or {}
    opencv = capture_meta.get("opencv_analysis", {}) or {}
    analysis = capture_meta.get("recognition_analysis", {}) or {}
    top = safe_get(manifest, "top_5_candidates", 0, default={}) or {}
    scores = top.get("scores", {}) or {}
    gt = metadata.get("ground_truth", {}) or {}
    real = manifest.get("ground_truth_product")
    timings = metadata.get("timings_ms", {}) or {}

    return [
        session.get("id"),
        manifest.get("_zip_name"),
        session.get("created_at"),
        session.get("status"),
        product_label(real),
        candidate_label(top),
        bool(real and top and top.get("product_id") == real.get("id")),
        gt.get("classification"),
        gt.get("rank"),
        round(float(top.get("score") or 0), 2) if top else None,
        round(float(metadata.get("auto_margin") or 0), 2) if metadata.get("auto_margin") is not None else None,
        ",".join(scores.get("candidate_sources") or []),
        scores.get("scoring_mode"),
        round(float(scores.get("region_score") or 0), 2) if scores.get("region_score") is not None else None,
        metadata.get("candidate_resources"),
        metadata.get("fingerprinted_candidates"),
        metadata.get("marker_augmented_candidates"),
        metadata.get("verification_pool_size"),
        metadata.get("verification_pool_added_candidates"),
        metadata.get("after_final_stage"),
        metadata.get("short_hash_protected_final"),
        timings.get("total"),
        timings.get("hash_search"),
        timings.get("orb"),
        capture_meta.get("detection_source"),
        capture_meta.get("cropped_client_side"),
        "yes" if opencv.get("contour") else "no",
        opencv.get("confidence"),
        opencv.get("normalization_rejected"),
        opencv.get("normalization_rejection_reason"),
        opencv.get("mode"),
        ",".join(analysis.get("structured_regions") or []),
    ]


def build_chat_summary_rows() -> tuple[list[list], list[list], list[list]]:
    summary = [[
        "Iteracao", "Label", "Scans", "Corretas", "Accuracy %", "Latencia media ms",
        "Latencia mediana ms", "Latencia min ms", "Latencia max ms", "Accuracy reportada", "Notas",
    ]]
    scans = [["Iteracao", "Scan", "Status", "Produto top1", "Quality", "Score", "Latency ms", "Reason"]]
    chart = [["Iteracao", "Latencia mediana ms", "Accuracy %", "Indice user value"]]

    baseline_latency = 25000
    for key, batch in SCAN_BATCHES.items():
        rows = batch["rows"]
        latencies = [float(r[5]) for r in rows if r[5] is not None]
        accuracy = pct(batch["correct"], batch["total"])
        med = median(latencies)
        summary.append([
            key,
            batch["label"],
            len(rows) if rows else batch["total"],
            batch["correct"],
            accuracy,
            avg(latencies),
            med,
            min(latencies) if latencies else None,
            max(latencies) if latencies else None,
            batch["reported_accuracy"],
            batch["notes"],
        ])
        for row in rows:
            scans.append([key, *row])
        if med and accuracy is not None:
            latency_gain = max(0, min(100, (1 - (med / baseline_latency)) * 100))
            user_value = round((latency_gain * 0.45) + (accuracy * 0.55), 1)
        else:
            user_value = None
        chart.append([key, med, accuracy, user_value])

    return summary, scans, chart


def build_zip_rows(manifests: list[dict]) -> list[list]:
    rows = [[
        "Session", "Zip", "Created at", "Status", "Ground truth", "Top1", "Top1 correct",
        "GT classification", "GT rank", "Top1 score", "Auto margin", "Candidate source",
        "Scoring mode", "Region score", "Candidate resources", "Fingerprinted", "Marker augmented",
        "Verification pool size", "Verification added", "After final", "Short hash protected",
        "Latency total ms", "Hash search ms", "ORB ms", "Detection source", "Cropped client side",
        "OpenCV contour", "OpenCV confidence", "Normalization rejected", "Rejection reason",
        "OpenCV mode", "Structured regions",
    ]]
    for manifest in manifests:
        rows.append(zip_session_row(manifest))
    return rows


def build_ab_rows(manifests_by_id: dict[str, dict]) -> list[list]:
    rows = [[
        "A/B", "Before", "Before status", "Before top1", "Before class", "Before correct",
        "Before score", "Before margin", "Before contour", "Before confidence",
        "After", "After status", "After top1", "After class", "After correct",
        "After score", "After margin", "After contour", "After confidence",
        "Change", "Interpretation",
    ]]
    for test in AB_TESTS:
        before = manifests_by_id.get(test["before"])
        after = manifests_by_id.get(test["after"])
        if not before or not after:
            continue
        b = zip_session_row(before)
        a = zip_session_row(after)
        rows.append([
            test["label"],
            test["before"], b[3], b[5], b[7], b[6], b[9], b[10], b[26], b[27],
            test["after"], a[3], a[5], a[7], a[6], a[9], a[10], a[26], a[27],
            test["change"], test["interpretation"],
        ])
    return rows


def build_top5_rows(manifests: list[dict]) -> list[list]:
    rows = [[
        "Session", "Rank", "Product ID", "Reference", "Name", "Score", "Region score",
        "pHash", "Edge", "Color", "Embedding", "ORB", "Sources",
    ]]
    for manifest in manifests:
        session_id = safe_get(manifest, "session", "id")
        for candidate in manifest.get("top_5_candidates") or []:
            scores = candidate.get("scores", {}) or {}
            rows.append([
                session_id,
                candidate.get("rank"),
                candidate.get("product_id"),
                candidate.get("reference"),
                candidate.get("name"),
                round(float(candidate.get("score") or 0), 2),
                round(float(scores.get("region_score") or 0), 2) if scores.get("region_score") is not None else None,
                scores.get("phash_score"),
                scores.get("edge_score"),
                scores.get("color_score"),
                scores.get("embedding_score"),
                scores.get("orb_score") or scores.get("orb"),
                ",".join(scores.get("candidate_sources") or []),
            ])
    return rows


def write_xlsx(path: Path, sheets: list[tuple[str, list[list]]]):
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
            f'<sheet name="{escape(name)}" sheetId="{i}" r:id="rId{i}"/>'
            for i, (name, _) in enumerate(sheets, start=1)
        )
        zf.writestr("xl/workbook.xml", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets>{sheet_defs}</sheets>
</workbook>""")
        rels = "\n".join(
            f'<Relationship Id="rId{i}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet{i}.xml"/>'
            for i in range(1, len(sheets) + 1)
        )
        zf.writestr("xl/_rels/workbook.xml.rels", f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">{rels}</Relationships>""")
        for i, (_, rows) in enumerate(sheets, start=1):
            zf.writestr(f"xl/worksheets/sheet{i}.xml", sheet_xml(rows))


def write_report(path: Path, chat_summary: list[list], zip_rows: list[list], ab_rows: list[list]):
    latest = {str(row[0]): row for row in zip_rows[1:]}
    lines = [
        "# WebCatalogue Visual Recognition - Relatorio Completo de Evolucao",
        "",
        f"Data: {TODAY}",
        "",
        "## Resumo executivo",
        "",
        "O modulo de reconhecimento visual evoluiu de uma prova de conceito lenta para uma pipeline auditavel, progressiva e preparada para demonstracao academica. O ponto mais importante nao e apenas a melhoria absoluta, mas a demonstracao do caminho: cada alteracao foi motivada por uma falha observada, seguida de novo teste e nova medicao.",
        "",
        "O baseline reportado tinha aproximadamente 25 segundos de latencia e 3-4 acertos em 12 scans. A fase consolidada com scoring/consenso atingiu 11 acertos em 15 scans, com mediana de cerca de 2.9 segundos. Nos testes A/B mais recentes, os ZIPs permitiram demonstrar regressao, causa e correcao: por exemplo, a sessao 417 falhou top 5 e a 418, apos a correcao de full-card visibility/normalizacao, acertou top 1.",
        "",
        "## Evolucao quantitativa consolidada",
        "",
        "| Iteracao | Accuracy | Latencia mediana | Leitura |",
        "|---|---:|---:|---|",
    ]
    for row in chat_summary[1:]:
        lines.append(f"| {row[0]} - {row[1]} | {row[4] if row[4] is not None else '-'}% | {row[6] if row[6] is not None else '-'} ms | {row[10]} |")

    lines += [
        "",
        "## A/B tests com ZIPs diagnosticos",
        "",
        "| Comparacao | Antes | Depois | Interpretacao |",
        "|---|---|---|---|",
    ]
    for row in ab_rows[1:]:
        before = f"{row[1]}: {row[2]}, {row[3]}, {row[4]}, correct={row[5]}, score={row[6]}"
        after = f"{row[10]}: {row[11]}, {row[12]}, {row[13]}, correct={row[14]}, score={row[15]}"
        lines.append(f"| {row[0]} | {before} | {after} | {row[20]} |")

    lines += [
        "",
        "## Timeline tecnica das alteracoes",
        "",
        "| Passo | Alteracao | Resultado observado | Valor cientifico |",
        "|---|---|---|---|",
    ]
    for row in IMPLEMENTATION_TIMELINE:
        lines.append(f"| {row[0]} | {row[2]} | {row[3]} | {row[4]} |")

    lines += [
        "",
        "## Observacoes por ZIP diagnostico",
        "",
        "Os ZIPs passaram a ser uma ferramenta central de diagnostico: contem captura, crop/normalizacao, top 5 candidatos, candidato real e metadata do matching. Isto permitiu distinguir falhas de ranking de falhas de captura.",
        "",
        "- 412 -> 413: regressao por eliminacao do candidato certo do top 5; solucao: proteger candidatos short_hash na fase final.",
        "- 414 -> 415: regressao por captura mal enquadrada; OpenCV sem contorno e confidence 0; solucao: rejeitar normalizacao invalida e exigir carta inteira visivel.",
        "- 417 -> 418: melhoria confirmada; mesmo produto passou de missed_top_5 para top_1_match.",
        "",
        "## Conclusao",
        "",
        "O projeto demonstra uma estrategia cientificamente defensavel: medir, identificar gargalos, formular hipotese, alterar a pipeline, testar e comparar. A evolucao da latencia prova o valor da exclusao progressiva; a evolucao de accuracy prova a importancia de normalizacao orientada ao dominio e de preservar sinais fortes como short_hash.",
    ]
    path.write_text("\n".join(lines), encoding="utf-8")


def report_html(markdown_text: str) -> str:
    body = []
    in_list = False
    for raw in markdown_text.splitlines():
        line = raw.strip()
        if not line:
            if in_list:
                body.append("</ul>")
                in_list = False
            continue
        if line.startswith("# "):
            if in_list:
                body.append("</ul>")
                in_list = False
            body.append(f"<h1>{html.escape(line[2:])}</h1>")
        elif line.startswith("## "):
            if in_list:
                body.append("</ul>")
                in_list = False
            body.append(f"<h2>{html.escape(line[3:])}</h2>")
        elif line.startswith("- "):
            if not in_list:
                body.append("<ul>")
                in_list = True
            body.append(f"<li>{html.escape(line[2:])}</li>")
        elif line.startswith("|"):
            if in_list:
                body.append("</ul>")
                in_list = False
            body.append(f"<pre>{html.escape(line)}</pre>")
        else:
            if in_list:
                body.append("</ul>")
                in_list = False
            body.append(f"<p>{html.escape(line)}</p>")
    if in_list:
        body.append("</ul>")

    return f"""<!doctype html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>WebCatalogue Visual Recognition - Relatorio Completo</title>
<style>
body{{font-family:Arial,sans-serif;margin:42px;color:#172033;line-height:1.52;background:#fff}}
h1,h2{{color:#0f172a}} h1{{font-size:28px}} h2{{font-size:20px;margin-top:28px}}
p,li,pre{{font-size:13px}} pre{{white-space:pre-wrap;border:1px solid #d7dde8;background:#f7f9fc;padding:8px;border-radius:6px}}
ul{{padding-left:20px}} .shell{{max-width:1040px;margin:auto}}
</style>
</head>
<body><main class="shell">{''.join(body)}</main></body>
</html>"""


def pdf_escape(text: str) -> str:
    return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def write_pdf(path: Path, text: str):
    logical_lines = []
    for line in text.splitlines():
        if not line.strip():
            logical_lines.append("")
            continue
        logical_lines.extend(textwrap.wrap(line, width=92) or [""])

    pages = [logical_lines[i:i + 42] for i in range(0, len(logical_lines), 42)] or [[]]
    objects = [
        "<< /Type /Catalog /Pages 2 0 R >>",
        f"<< /Type /Pages /Kids [{' '.join(f'{3 + i * 2} 0 R' for i in range(len(pages)))}] /Count {len(pages)} >>",
    ]

    for index, page_lines in enumerate(pages):
        page_obj = 3 + index * 2
        content_obj = page_obj + 1
        objects.append(
            f"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] "
            f"/Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> "
            f"/Contents {content_obj} 0 R >>"
        )
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
    manifests = all_zip_manifests()
    manifests_by_id = {str(safe_get(m, "session", "id")): m for m in manifests}
    chat_summary, chat_scans, chart_rows = build_chat_summary_rows()
    zip_rows = build_zip_rows(manifests)
    ab_rows = build_ab_rows(manifests_by_id)
    top5_rows = build_top5_rows(manifests)
    timing_rows = [["Iteracao", "Componente", "Tempo ms"], *TIMINGS]
    timeline_rows = [["Passo", "Nome", "Alteracao", "Resultado", "Valor cientifico"], *IMPLEMENTATION_TIMELINE]

    xlsx_path = ROOT / "webcatalogue_recognition_full_evolution_2026-05-23.xlsx"
    report_path = ROOT / "webcatalogue_recognition_full_evolution_report_2026-05-23.md"
    html_path = ROOT / "webcatalogue_recognition_full_evolution_report_2026-05-23.html"
    pdf_path = ROOT / "webcatalogue_recognition_full_evolution_report_2026-05-23.pdf"

    write_xlsx(xlsx_path, [
        ("Evolucao", chat_summary),
        ("Scans_Chat", chat_scans),
        ("Dados_Grafico", chart_rows),
        ("ZIP_Sessions", zip_rows),
        ("AB_Tests", ab_rows),
        ("Top5_Candidates", top5_rows),
        ("Tempos", timing_rows),
        ("Timeline", timeline_rows),
    ])
    write_report(report_path, chat_summary, zip_rows, ab_rows)
    markdown = report_path.read_text(encoding="utf-8")
    html_path.write_text(report_html(markdown), encoding="utf-8")
    write_pdf(pdf_path, markdown)
    print(f"Generated {xlsx_path}")
    print(f"Generated {report_path}")
    print(f"Generated {html_path}")
    print(f"Generated {pdf_path}")


if __name__ == "__main__":
    main()

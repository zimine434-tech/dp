<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Приказ о месте проведения занятий</title>
    <style>
        @page {
            margin: 2cm 2.5cm;
        }
        * {
            font-family: 'times new roman', 'TimesNewRoman', Times, serif;
            font-size: 14pt;
        }
        body {
            font-family: 'times new roman', 'TimesNewRoman', Times, serif;
            font-size: 14pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: left;
            margin-bottom: 10px;
        }
        .header-title {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .order-subtitle {
            font-size: 14pt;
            margin-bottom: 15px;
        }
        .order-content {
            text-align: justify;
            margin-bottom: 12px;
        }
        .order-content p {
            margin: 0;
        }
        .order-command {
            font-size: 14pt;
            margin: 12px 0 10px 0;
            font-weight: bold;
        }
        .order-items {
            margin: 10px 0;
        }
        .order-item {
            margin-bottom: 8px;
            text-align: justify;
            page-break-inside: avoid;
        }
        .order-item-number {
        }
        .preamble {
            text-align: justify;
            margin: 15px 0;
        }
        .signature-section {
            margin-top: 20px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-label {
            text-align: left;
            width: 50%;
        }
        .signature-name {
            text-align: right;
            width: 50%;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title" style="font-weight: bold;">Проект приказа</div>
        <div class="order-subtitle" style="font-weight: bold;">«О месте проведения занятий по дисциплине «Физическая культура»»</div>
    </div>

    <div class="preamble">
        С целью выполнения требований ФГОС СПО, ФГОС СОО, федеральной рабочей программы по учебному предмету «Физическая культура» к результатам освоения дисциплины «Физическая культура»
    </div>

    <div class="order-command">ПРИКАЗЫВАЮ:</div>

    <div class="order-items">
        <div class="order-item">
            <span class="order-item-number">1. </span>
            Разрешить преподавателям Кудрявцеву Н.В., Уманцу А.В., Кулиш Е.А., Аленевской А.А. проводить учебные занятия по дисциплине «Физическая культура», в том числе в рамках самостоятельной работы, на {{ $location_classes }}.
        </div>

        <div class="order-item">
            <span class="order-item-number">2. </span>
            Преподавателям Кудрявцеву Н.В., Уманцу А.В., Кулиш Е.А., Аленевской А.А. провести с обучающимися под роспись инструктаж по правилам техники безопасности (Приложение 1).
        </div>

        <div class="order-item">
            <span class="order-item-number">3. </span>
            Контроль за исполнением приказа возложить на Аленевскую А.А., руководителя физического воспитания.
        </div>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="signature-label">Директор</td>
                <td class="signature-name">А. Н. Якубовский</td>
            </tr>
        </table>
    </div>
</body>
</html>


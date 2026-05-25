<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Приказ об участии в мероприятии</title>
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
        <div class="order-subtitle" style="font-weight: bold;">«Об участии в мероприятии»</div>
    </div>

    <div class="order-content">
        <p>
            {{ $start_date }}@if($start_date != $end_date) - {{ $end_date }}@endif года {{ $location_full ?? ($location . ($location_address ? ', ' . $location_address : '')) }} пройдут {{ $competition_name }}@if($sport_name) по виду спорта {{ $sport_name }}@endif (далее – соревнования)
        </p>
    </div>

    <div class="order-command">ПРИКАЗЫВАЮ:</div>

    <div class="order-items">
        <div class="order-item">
            <span class="order-item-number">1. </span>
            Назначить {{ $teacher_name }}, преподавателя, сопровождающим обучающихся техникума – участников соревнований.
        </div>

        <div class="order-item">
            <span class="order-item-number">2. </span>
            Заведующей учебной частью {{ $head_of_studies }} скорректировать расписание учебных занятий с учётом задействования преподавателя в соревнованиях.
        </div>

        <div class="order-item">
            <span class="order-item-number">3. </span>
            Контроль за исполнением приказа возложить на заместителя директора {{ $deputy_director }}.
        </div>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="signature-label">Директор</td>
                <td class="signature-name">{{ $director_name }}</td>
            </tr>
        </table>
    </div>
</body>
</html>


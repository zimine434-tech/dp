<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Именная заявка</title>
    <style>
        @page { margin: 1.5cm 1.8cm; }
        * { font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        body { margin: 0; padding: 0; color: #000; line-height: 1.2; }
        .title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 8px; }
        .subtitle { text-align: center; margin-bottom: 4px; }
        .competition-line { text-align: center; font-weight: bold; text-decoration: underline; margin: 6px 0 10px; min-height: 18px; }
        .meta-row { margin: 8px 0; text-align: center; }
        .meta-underline { display: inline-block; min-width: 280px; border-bottom: 1px solid #000; text-align: center; padding: 0 4px 2px; }
        .meta-hint { font-size: 10pt; text-align: center; color: #000; }
        table.roster { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.roster th, table.roster td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: middle;
            font-size: 10pt;
        }
        table.roster th { text-align: center; font-weight: bold; font-size: 9pt; }
        table.roster td.num { text-align: center; width: 28px; }
        table.roster td.name { text-align: left; }
        table.roster td.center { text-align: center; }
        .after-table { margin-top: 10px; font-size: 11pt; }
        .admitted-line { margin: 8px 0; }
        .doctor-block { margin: 10px 0; font-size: 11pt; }
        .team-line { margin: 8px 0; }
        .signatures { margin-top: 16px; font-size: 11pt; }
        .sign-row { margin: 12px 0; }
        .sign-spacer { display: inline-block; min-width: 10px; }
        .sign-row-table { width: 100%; border-collapse: collapse; }
        .sign-row-table td { padding: 0; vertical-align: bottom; }
        .sign-row-label { padding-right: 10px; }
        .sign-row-right { text-align: right; }
        .sign-table { display: inline-table; vertical-align: middle; border-collapse: collapse; }
        .sign-table td { padding: 0; vertical-align: bottom; }
        .sign-line-cell { width: 190px; border-bottom: 1px solid #000; height: 14px; }
        .sign-slash-cell { width: 20px; text-align: center; }
        .sign-hint-cell { width: 190px; text-align: center; font-size: 10pt; padding-top: 2px; }
        .footer-date { margin-top: 20px; text-align: left; font-size: 11pt; }
        .mp { margin-top: 8px; }
    </style>
</head>
<body>
    <div class="title">ИМЕННАЯ ЗАЯВКА</div>
    <div class="subtitle">на участие в</div>
    <div class="competition-line">{{ $competition_name }}</div>
    <div class="subtitle">среди студентов профессиональных образовательных организаций</div>
    <div class="subtitle" style="margin-bottom: 14px;">Иркутской области</div>

    <div class="meta-row">
        команда по <span class="meta-underline">{{ $sport_name }}</span>
    </div>
    <div class="meta-hint">(наименование вида программы)</div>

    <div class="meta-row" style="margin-top: 10px;">
        от <span class="meta-underline">{{ $institution_name }}</span>
    </div>
    <div class="meta-hint">(полное наименование профессиональной образовательной организации)</div>

    <table class="roster">
        <thead>
            <tr>
                <th class="num">№<br>п/п</th>
                <th>ФИО (полностью)</th>
                <th>Дата<br>рождения</th>
                <th>№ студенческого<br>билета</th>
                <th>Виза врача<br>(дата, подпись, печать)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($table_rows as $row)
                <tr>
                    <td class="num">{{ $row['index'] }}</td>
                    <td class="name">{{ $row['full_name'] }}</td>
                    <td class="center">{{ $row['birth_date'] }}</td>
                    <td class="center">{{ $row['student_card'] }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="after-table">
        <div class="admitted-line">Всего допущено _________________ чел.</div>

        <div class="doctor-block">
            <div>ФИО врача (полностью) _________________________________________________</div>
            <div style="margin-top: 6px; font-size: 10pt;">
                Печать медицинского учреждения, в котором работает врач, заверяющего допуск к участию в соревнованиях
            </div>
        </div>

        <div class="team-line">
            Состав команды — <strong>{{ $students_count }}</strong> {{ $people_label }},
            в том числе <strong>{{ $students_count }}</strong> {{ $participants_label }}.
        </div>
    </div>

    <div class="signatures">
        <div class="sign-row">
            <table class="sign-row-table">
                <tr>
                    <td class="sign-row-label">Директор профессиональной образовательной организации</td>
                    <td class="sign-row-right">
                        <table class="sign-table">
                            <tr>
                                <td class="sign-line-cell"></td>
                                <td class="sign-slash-cell">/</td>
                                <td class="sign-line-cell"></td>
                            </tr>
                            <tr>
                                <td class="sign-hint-cell">(подпись)</td>
                                <td class="sign-slash-cell"></td>
                                <td class="sign-hint-cell">(расшифровка подписи)</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="sign-row">
            <table class="sign-row-table">
                <tr>
                    <td class="sign-row-label">Руководитель физического воспитания</td>
                    <td class="sign-row-right">
                        <table class="sign-table">
                            <tr>
                                <td class="sign-line-cell"></td>
                                <td class="sign-slash-cell">/</td>
                                <td class="sign-line-cell"></td>
                            </tr>
                            <tr>
                                <td class="sign-hint-cell">(подпись)</td>
                                <td class="sign-slash-cell"></td>
                                <td class="sign-hint-cell">(расшифровка подписи)</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="mp">М.П.</div>
    </div>

    <div class="footer-date">{{ $document_date_line }}</div>
</body>
</html>

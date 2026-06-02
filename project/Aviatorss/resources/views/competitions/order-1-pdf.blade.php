<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Приказ об освобождении от учебных занятий</title>
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
            text-align: center;
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
        .students-list {
            margin: 5px 0;
            padding-left: 0;
            list-style: none;
            page-break-inside: avoid;
        }
        .students-list li {
            margin-bottom: 1px;
            padding-left: 20px;
            text-indent: -20px;
            line-height: 1.15;
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
        <div class="header-title">Проект приказа</div>
        <div class="order-subtitle">Об освобождении от учебных занятий</div>
    </div>

    <div class="order-content">
        <p>
            {{ $start_date }}@if($start_date != $end_date) - {{ $end_date }}@endif года {{ $location_full }} пройдёт {{ $competition_description }}
        </p>
    </div>

    <div class="order-command">ПРИКАЗЫВАЮ:</div>

    <div class="order-items">
        <div class="order-item">
            <span class="order-item-number">1. </span>
            Освободить от учебных занятий {{ $start_date }}@if($start_date != $end_date) - {{ $end_date }}@endif года и направить на соревнования следующих обучающихся:
            @if($students_count > 0)
                <ul class="students-list">
                    @foreach($students as $index => $student)
                        <li>
                            {{ $index + 1 }}) {{ $student->user->lastname }} {{ $student->user->firstname }} {{ $student->user->patronymic ?? '' }}{{ $student->user->group_name ? ', ' . $student->user->group_name : '' }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p style="margin-top: 10px; font-style: italic;">Список обучающихся будет добавлен</p>
            @endif
        </div>

        <div class="order-item">
            <span class="order-item-number">2. </span>
            Возложить ответственность за жизнь и здоровье обучающихся и назначить ответственным за сопровождение обучающихся до места проведения соревнований и обратно, участие, соблюдение техники безопасности, и поведение обучающихся на время проведения соревнований {{ $accompanying_teacher }}.
        </div>

        <div class="order-item">
            <span class="order-item-number">3. </span>
            Аленевской А.А., руководителю физ. воспитания, провести инструктаж с обучающимися по вопросам соблюдения техники безопасности под роспись.
        </div>

        <div class="order-item">
            <span class="order-item-number">4. </span>
            {{ $dispatcher }}, диспетчеру, ознакомить кураторов групп с данным приказом под роспись.
        </div>

        <div class="order-item">
            <span class="order-item-number">5. </span>
            Контроль за исполнением настоящего приказа возложить на {{ $deputy_director }}, заместителя директора.
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


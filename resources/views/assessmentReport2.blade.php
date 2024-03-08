<!DOCTYPE html>
<html>
<head>
    <title>Reporte por funcionario EDA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></head>
<body class="mx-5 my-5">


<img src="/images/whiteLogo.png"
     style="max-height: 300px; max-width:300px; object-fit: contain" alt="Logo_universidad">

<p  style="margin-top: 30px"> Sistema de Información para evaluación de funcionarios <strong> EDA </strong></p>

<p> Periodo de evaluación: <strong> {{$assessmentPeriodName}} </strong> </p>

<p> <strong> Reporte para resultados de evaluación</strong> </p>

<p style="margin-bottom: 30px"> Visualizando al funcionario: <strong> {{ucwords($functionaryName)}} </strong></p>

<table class="table" style="max-width: 85%; margin: auto" >
    <thead>
    <tr>
        <th scope="col">Rol</th>



        @foreach($labels as $label)
            @if($role === 'administrador')
                <th scope="col">{{$label->name}}</th>
            @else
                <th scope="col">{{$label}}</th>
            @endif
        @endforeach



    </tr>
    </thead>
    <tbody>
    @foreach($grades as $grade)   <!--Here we iterate through the array of objects-->
    <tr>
        @foreach ($grade as $property => $value)      <!--Here we iterate over the single object properties and print the value of the property -->
            <td>{{$value}}</td>
        @endforeach
    </tr>
    @endforeach
    </tbody>
</table>


<p> <strong> Graficación de Resultados </strong> </p>
<canvas id="graph"></canvas>





{{--Aquí van las respuestas a las preguntas abiertas de los formularios--}}

@if(count($openAnswers) > 0)
    <div style="margin-top: 30px">
        <p style="font-weight: bold"> Comentarios de la evaluación </p>
        @foreach($openAnswers as $question)
            <p class="black--text pt-2"> Pregunta: </p>
            <p style="font-weight: bold; margin-left: 10px">{{$question->name}}</p>
            <div style="margin-left: 20px">
                @foreach($question->answers as $person)
                        <p>{{$person->answer}}</p>
                @endforeach
            </div>
        @endforeach
    </div>
@endif
<h6 style="margin-top: 100px; font-weight: bold" > Reporte generado en: {{\Carbon\Carbon::now('GMT-5')}}</h6>
</body>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('graph').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: '# of Votes',
                data: [12, 19, 3, 5, 2, 3],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</html>

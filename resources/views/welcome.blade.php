<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hierarchy</title>
    <style>
        .graph table,
        .graph {
            border-spacing: 0;
        }

        .graph td {
            vertical-align: top;
            padding: 0;
            height: 10px;
            box-sizing: border-box;
        }

        .graph td:empty {
            min-width: 10px
        }

        .graph table td:not(:empty) {
            border-radius: 20px;
            background: pink;
            border: solid;
            padding: 10px;
            text-align: center;
        }

        .graph table {
            width: 100%
        }

        .graph table td {
            width: 50%
        }

        .graph table td:nth-child(2) {
            border-left: solid;
        }

        .graph .branch {
            border-top: solid;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>

    <div id="form">
        <input type="tel" required name="" value="1" id="user_id" placeholder="Enter User Id">
        <button type="button" onclick="call();" id="submit">Submit</button>
    </div>


    {{-- <button>Randomize!</button> --}}
    {{-- <table class="graph"></table> --}}
    <table class="graph" id="graph"></table>

    {{-- <script src="{{ asset('app1.js') }}"></script> --}}
    <script src="{{ asset('app.js') }}"></script>
    <script>
        $('#form').submit(function(e) {
            e.preventDefault();
            var user_id = $('#user_id').val();
            demo(user_id);
        });

        function call(){
            var user_id = $('#user_id').val();
            if(user_id == '' || user_id == null){
                alert('Please enter user id');
                return false;
            }
            demo(user_id);
        }
    </script>

</body>

</html>

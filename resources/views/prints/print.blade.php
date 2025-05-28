<!DOCTYPE html>
<html>

<head>
    <script>
    window.onload = function() {
        window.print();
        setTimeout(function() {
            window.location.href = "{{ $return_url }}";
        }, 500);
    };
    </script>
</head>

<body>
    @include('invoices.index', ['transactions' => $transactions])
</body>

</html>
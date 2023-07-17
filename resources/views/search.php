<!-- View stored in resources/views/barSearch.blade.php -->
<html>
<head>
    <title>Bar Search</title>
</head>
<body>
    <h1>Bar Search</h1>
    <form method="GET" action="/barSearch">
        <input type="text" name="string" placeholder="Enter a string">
        <button type="submit">Search</button>
    </form>
    @if ($data)
        <h2>Results</h2>
        <table border="1">
            <tr>
                <th>Product Name</th>
                <th>Product ID</th>
                <th>Drug Name</th>
                <th>Drug ID</th>
            </tr>
            @foreach ($data[0] as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->product_id }}</td>
                    @if ($loop->first)
                        @foreach ($data[1] as $drug)
                            <td>{{ $drug->drug_name }}</td>
                            <td>{{ $drug->drug_id }}</td>
                        @endforeach
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @else
        <p>No matches to return.</p>
    @endif
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rent Receipt</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Tahoma, Arial, sans-serif;
      width: 58mm;
      margin: 0 auto;
      padding: 0;
      font-size: 11px;
      background: #fff;
    }
    .invoice-container {
      width: 100%;
    }
    h2 {
      text-align: center;
      margin: 0 0 4px 0;
      font-size: 15px;
      font-weight: bold;
    }
    .address {
      text-align: center;
      font-size: 10px;
      margin: 0 0 8px;
    }
    .meta {
      font-size: 10px;
      margin-bottom: 6px;
      line-height: 1.4;
    }
    .meta strong {
      display: inline-block;
      width: 80px;
    }
    .line-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-bottom: 6px;
    }
    .line-table th, .line-table td {
      border-bottom: 1px dashed #000;
      padding: 2px 0;
    }
    .line-table th {
      text-align: left;
      font-weight: bold;
    }
    .line-table td:last-child, .line-table th:last-child {
      text-align: right;
    }
    .urdu {
      text-align: center;
      margin-top: 8px;
      font-family: 'Noto Nastaliq Urdu', serif;
      font-size: 12px;
    }
    .footer {
      font-size: 9px;
      margin-top: 10px;
      text-align: left;
    }

    @media print {
      body {
        width: 70mm;
        margin: 5px;
        padding: 5px;
      }
      .footer {
        margin-top: 6px;
      }
    }
  </style>
</head>
<body>
  <div class="invoice-container">
    <h2><strong>{{ $invoice->customer->building->name }}</strong></h2>
    <div class="address">{{ $invoice->customer->building->address }}</div>
    <div class="address">{{ $invoice->customer->building->contact_person }} {{ $invoice->customer->building->contact }}</div>

    <div class="meta">
      <strong>Name:</strong> {{ $invoice->customer->name }}<br>
      <strong>Contact:</strong> {{ $invoice->customer->mobile_no }}<br>
      <strong>Shop/Room:</strong>
      @foreach($invoice->customer->rooms() as $room)
        {{ $room->no }}{{ !$loop->last ? ',' : '' }}
      @endforeach <br>
      <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format("d-M-Y") }}
    </div>

    <table class="line-table">
      <tr>
        <th>Month</th>
        <td>{{ $invoice->month }}-{{ $invoice->year }}</td>
      </tr>
      <tr>
        <th>Monthly Rent</th>
        <td>{{ number_format($invoice->rent_amount, 2) }}</td>
      </tr>
      <tr>
        <th>Previous Dues</th>
        <td>{{ number_format($invoice->dues, 2) }}</td>
      </tr>
      <tr>
        <th>Total Amount</th>
        <td>{{ number_format($invoice->total, 2) }}</td>
      </tr>
      
       
    </table>

    <table class="line-table">
      <tr>
        <th>Payment Status</th>
        <th>Total Paid</th>
        <th colspan="2">Current Dues</th>
      </tr>
      <tr>
        <td>{{ $invoice->status }}</td>
        <td>{{ number_format($invoice->paid, 2) }}</td>

        <td colspan="2" style="text-align: right;">{{ number_format($invoice->remaining, 2) }}</td>
      </tr>
    </table>

    <div class="urdu">
      برائے کرم 5 تاریخ سے پہلے ادائیگی ادا کریں۔ شکریہ
    </div>

    <div class="footer">
      Software developed by AppFlex Technology +9232-928-2424
      <hr>
    </div>
  </div>

  <script>
    window.onload = function () {
      window.print();
      window.onafterprint = function () {
        const urlPattern = /\/invoice\/\d+\/print$/;
        if (urlPattern.test(window.location.href)) {
          window.location.href = "{{ route('invoices.index') }}";
        }
      };
    };
  </script>
</body>
</html>

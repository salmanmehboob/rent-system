<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rent Receipt</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      width: 700px;
      margin: auto;
      padding: 20px;
    }
    h2 {
      text-align: center;
      margin: 0;
    }
    .address {
      text-align: center;
      font-size: 14px;
      margin-bottom: 20px;
    }
    .meta {
      font-size: 14px;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .meta strong {
      display: inline-block;
      width: 130px;
    }

    .line-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .line-table td, .line-table th {
      border-bottom: 1px solid #000;
      padding: 8px 0;
    }

    .line-table td:first-child, .line-table th:first-child {
      text-align: left;
    }

    .line-table td:last-child, .line-table th:last-child {
      text-align: right;
    }

    .urdu {
      text-align: center;
      margin-top: 20px;
      font-family: 'Noto Nastaliq Urdu', serif;
      font-size: 16px;
    }

    .footer {
      text-align: left;
      font-size: 12px;
      margin-top: 40px;
    }

    @media print {
      body {
        width: auto;
        margin: 0;
      }
    }
  </style>
</head>
<body>

  <h2><strong>AppFlex Technology</strong></h2>
  <div class="address">
    Al-Sadiq Plaza, F-109-110, Old Post Office Road,<br>
    Mingora Swat Mingora, Khyber Pakhtunkhwa,<br>
    Pakistan - 19130
  </div>

  <div class="meta">
    <strong>Customer Name:</strong> {{ $transaction->customer->name }}<br>
    <strong>Contact:</strong> {{ $transaction->customer->mobile_no }}<br>
    <strong>Shop/Room:</strong>
    @foreach($transaction->customer->rooms as $room)
    {{ $room->no }}{{ !$loop->last ? ',' : '' }}
    @endforeach <br>
    <strong>Date:</strong> {{ \Carbon\Carbon::parse($transaction->created_at)->format("d-M-Y") }}
  </div>

  <table class="line-table">
    <tr>
      <th>Month</th>
      <td>{{ $transaction->month }}</td>
    </tr>
    <tr>
      <th>Monthly Rent</th>
      <td>{{ $transaction->rent_amount }}</td>
    </tr>
    <tr>
      <th>Previous Dues</th>
      <td>{{ $transaction->previous_dues }}</td>
    </tr>
    <tr>
      <th>Grand Total</th>
      <td>{{ $transaction->sub_total }}</td>
    </tr>
  </table>

  <table class="line-table">
    <tr>
      <th>Payment Status</th>
      <th>Payable Amount</th>
      <th>Current Dues</th>
    </tr>
    <tr>
      <td>{{ $transaction->status }}</td>
      <td style="text-align: center; ">{{ $transaction->payable_amount }}</td>
      <td style="text-align: right;">{{ $transaction->current_dues }}</td>
    </tr>
  </table>

  <div class="urdu">
    برائے کرم 5 تاریخ سے پہلے ادائیگی ادا کریں۔ شکریہ
  </div>

  <div class="footer">
    Software developed by AppFlex Technology +9232-928-2424
  </div>




  
      <script>
    window.onload = function() {
        window.print();

        // This will trigger after user prints or cancels
        window.onafterprint = function() {
            // Check if the current URL matches the print invoice route pattern
            const urlPattern = /\/invoice\/\d+\/print$/;

            if (urlPattern.test(window.location.href)) {
                // Redirect to POS index route after print dialog is closed
                window.location.href = "{{ route('transactions.index') }}";
            }
        };
    };
    </script>
</body>
</html>


  




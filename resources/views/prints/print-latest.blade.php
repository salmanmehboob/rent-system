<!DOCTYPE html>
<html>
<head>
    <title>Latest Invoices</title>
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

    .invoice-page {
        padding: 20px;
        margin-top: 80px;
        border: 1px solid #ccc;
        border-radius: 8px;
        page-break-after: always;
        height: 700px;
        }

        @media print {
        .invoice-page {
            page-break-after: always;
        }

        .footer {
            text-align: left;
            font-size: 12px;
            margin-top: 80px;
        }

        body {
            margin: 0;
            padding: 0;
        }
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

 

    @media print {
      body {
        width: auto;
        margin: 0;
      }
    }
  </style>
</head>
<body>

    @foreach($invoices as $invoice)
       <div class="invoice-page">
            <h2><strong>AppFlex Technology</strong></h2>
            <div class="address">
                Al-Sadiq Plaza, F-109-110, Old Post Office Road,<br>
                Mingora Swat Mingora, Khyber Pakhtunkhwa,<br>
                Pakistan - 19130
            </div>

            <div class="meta">
                <strong>Customer Name:</strong> {{ $invoice->customer->name }}<br>
                <strong>Contact:</strong> {{ $invoice->customer->mobile_no }}<br>
                <strong>Property:</strong>
                @foreach($invoice->customer->rooms() as $room)
                {{ $room->type }}-{{ $room->no }}{{ !$loop->last ? ',' : '' }}
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
                <td>{{ $invoice->rent_amount }}</td>
                </tr>
                <tr>
                <th>Previous Dues</th>
                <td>{{ $invoice->dues }}</td>
                </tr>
                <tr>
                <th>Total paid</th>
                <td>{{ $invoice->paid }}</td>
                </tr>
                <tr>
                <th>Grand Total</th>
                <td>{{ $invoice->remaining }}</td>
                </tr>
            </table>

            <table class="line-table">
                <tr>
                <th>Payment Status</th>
                <th>Payable Amount</th>
                <th>Current Dues</th>
                </tr>
                <tr>
                <td>{{ $invoice->status }}</td>
                <td style="text-align: center; "></td>
                <td style="text-align: right;"></td>
                </tr>
            </table>

            <div class="urdu">
                برائے کرم 5 تاریخ سے پہلے ادائیگی ادا کریں۔ شکریہ
            </div>

            <div class="footer">
                Software developed by AppFlex Technology +9232-928-2424
            </div>
       </div>
    @endforeach

    <script>
        window.onload = function () {
            window.print();

            // This will trigger after user prints or cancels
            window.onafterprint = function() {
                // Check if the current URL matches the print invoice route pattern
                const urlPattern = /\/print-latest-invoices$/;

                if (urlPattern.test(window.location.href)) {
                    // Redirect to  home route after print dialog is closed
                    window.location.href = "{{ route('home') }}";
                }
            };
        };
    </script>
</body>
</html>

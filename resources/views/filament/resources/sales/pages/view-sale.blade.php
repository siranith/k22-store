<x-filament-panels::page>
    @php
        // Compact receipt styles for 57mm sticker printers
    @endphp

    <style>
        @page { size: 57mm auto; margin: 2mm; }
        @font-face {
            font-family: 'Khmer OS Battambang';
            src: url('/fonts/KhmerOSbattambang.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        .receipt {
            width: 57mm;
            max-width: 57mm;
            font-family: 'Khmer OS Battambang', Courier, monospace;
            font-size: 16px;
            line-height: 1.25;
            color: #000;
            background: transparent;
            padding: 5px;
        }
        .receipt .header {
            text-align: center;
            font-weight: 400;
            margin-bottom: 4px;
        }
        .receipt .meta { font-size: 18px; margin-bottom: 6px; font-weight: 400;}
        .receipt table { width: 100%; border-collapse: collapse; }
        .receipt th, .receipt td { padding: 2px 0; vertical-align: top; }
        .receipt .item { word-break: break-word; }
        .text-right { text-align: right; }
        .small { font-size: 10px; }
        .hr { border-top: 1px dashed #333; margin: 6px 0; }
    </style>
    <div>
        <div class="filament-page-header">
            <h2 class="filament-page-header-heading text-xl font-bold">
                Sale Receipt
            </h2>
            <table>
                <tr>
                    <td class="font-medium text-sm">Invoice #</td>
                    <td class="text-sm text-right">{{ $record->invoice_number ?? ('#' . $record->id) }}</td>
                </tr>
                <tr>
                    <td class="font-medium text-sm">Date</td>
                    <td class="text-sm text-right">{{ $record->created_at?->format('M d, Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="font-medium text-sm">Customer</td>
                    <td class="text-sm text-right">{{ $record->customer->name ?? 'Walk-in' }}</td>
                </tr>

            </table>
        </div>
    </div>
    <div class="receipt">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;padding-bottom:6px">
            <div style="margin-left:auto;display:flex;gap:6px;align-items:center;">
                <button id="saveImageBtn" type="button" style="font-size:11px;padding:4px;border:1px solid #333;background:#fff;border-radius:4px;cursor:pointer;">Save as Image</button>
                <button id="printBtn" type="button" style="font-size:11px;padding:4px 6px;border:1px solid #333;background:#fff;border-radius:4px;cursor:pointer;">Print</button>
            </div>
        </div>
        <div class="header text-center">
            <img
                src="{{ asset('storage/products/logo.png') }}"
                alt="Logo"
                class="mx-auto mb-1"
                style="max-width: 68px; height:auto;"
            >
        </div>
        <div class="meta small">
            <!-- <div>Sale: {{ $record->invoice_number ?? ('#' . $record->id) }}</div> -->
            <div>ទីតាំង: {{ $record->customer->address ?? $record->address ?? '—' }} </div>
            <div>ផ្ញើៈ 017​ 955 763</div>
            <!-- <div>ទទួល: {{ $record->customer->name ?? $record->contact_number ?? '—' }}</div> -->
            <div>ទទួល: {{ $record->customer->phone ?? $record->contact_number ?? '—' }}</div>
            <div>តម្លៃ: ${{ number_format($record->paid ?? 0, 2) }} {{ $record->cod ? '(COD)' : '' }}</div>
        </div>

        <div class="hr"></div>

        <table class="small">
            <thead>
                <tr>
                    <th class="item">Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grand = 0; @endphp
                @foreach ($record->saleItems as $item)
                    @php
                        $qty = $item->quantity ?? $item->qty ?? 0;
                        // Try common field names for line total or compute
                        $line = $item->line_total ?? ($item->unit_price ?? $item->price ?? 0) * $qty;
                        $grand += $line;
                    @endphp
                    <tr>
                        <td class="item">{{ \Illuminate\Support\Str::limit($item->product->name ?? $item->name ?? '—', 25) }}</td>
                        <td class="text-right">{{ $qty }}</td>
                        <td class="text-right">{{ number_format($line, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="hr"></div>

        <table style="width:100%">
            <tr>
                <td class="small">Discount</td>
                <td class="text-right small">{{ number_format($record->discount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="small">Amount</td>
                <td class="text-right small">{{ number_format(($record->total ?? $grand) - ($record->discount ?? 0), 2) }}</td>
            </tr>
        </table>

        <div class="hr"></div>
        <div class="small">{{ $record->created_at?->format('M d, Y H:i') }}</div>
        <div class="small" style="text-align:center;">Thank you</div>
    </div>

</x-filament-panels::page>

@once
    @push('scripts')
        <script>
            (function(){
                // Load html2canvas if not present
                function loadScript(src){
                    return new Promise(function(resolve, reject){
                        if (document.querySelector('script[src="' + src + '"]')) return resolve();
                        const s = document.createElement('script');
                        s.src = src;
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                }

                const saveBtn = document.getElementById('saveImageBtn');
                const printBtn = document.getElementById('printBtn');

                if (printBtn) {
                    printBtn.addEventListener('click', function(){
                        printBtn.style.display = 'none';
                        if (saveBtn) saveBtn.style.display = 'none';
                        window.print();
                        setTimeout(()=>{ printBtn.style.display = ''; if (saveBtn) saveBtn.style.display = ''; }, 500);
                    });
                }

                if (!saveBtn) return;

                async function ensureHtml2Canvas(){
                    if (window.html2canvas) return;
                    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js');
                }
                saveBtn.addEventListener('click', async function(){
    const prevSaveDisplay = saveBtn.style.display;
    const prevPrintDisplay = printBtn ? printBtn.style.display : null;
    try {
        saveBtn.style.display = 'none';
        if (printBtn) printBtn.style.display = 'none';

        await ensureHtml2Canvas();
        const el = document.querySelector('.receipt');
        if (!el) return alert('Receipt element not found');
        const scale = 2;
        const canvas = await html2canvas(el, { scale: scale, useCORS: true, logging: false });

        saveBtn.style.display = prevSaveDisplay;
        if (printBtn && prevPrintDisplay !== null) printBtn.style.display = prevPrintDisplay;

        canvas.toBlob(async function(blob){
            const a = document.createElement('a');
            const url = URL.createObjectURL(blob);
            const invoice = '{{ $record->invoice_number ?? $record->id }}';
            const filename = `receipt-${invoice}-${Date.now()}.png`;
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(()=> URL.revokeObjectURL(url), 1000);

            // ✅ After successful save, update sale.print = true
            try {
                await fetch("{{ route('sales.mark-printed', $record->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                });
                console.log('✅ Sale marked as printed.');
            } catch (err) {
                console.error('⚠️ Failed to update print status:', err);
            }
        }, 'image/png');
    } catch (e) {
        console.error(e);
        saveBtn.style.display = prevSaveDisplay;
        if (printBtn && prevPrintDisplay !== null) printBtn.style.display = prevPrintDisplay;
        alert('Failed to save image: ' + (e.message || e));
    }
});

                // saveBtn.addEventListener('click', async function(){
                //     // Hide buttons so they don't appear in the captured image
                //     const prevSaveDisplay = saveBtn.style.display;
                //     const prevPrintDisplay = printBtn ? printBtn.style.display : null;
                //     try{
                //         saveBtn.style.display = 'none';
                //         if (printBtn) printBtn.style.display = 'none';

                //         await ensureHtml2Canvas();
                //         const el = document.querySelector('.receipt');
                //         if (!el) return alert('Receipt element not found');
                //         const scale = 2;
                //         const canvas = await html2canvas(el, { scale: scale, useCORS: true, logging: false });

                //         // restore buttons right after snapshot is taken
                //         saveBtn.style.display = prevSaveDisplay;
                //         if (printBtn && prevPrintDisplay !== null) printBtn.style.display = prevPrintDisplay;

                //         canvas.toBlob(function(blob){
                //             const a = document.createElement('a');
                //             const url = URL.createObjectURL(blob);
                //             const invoice = '{{ $record->invoice_number ?? $record->id }}';
                //             const filename = `receipt-${invoice}-${Date.now()}.png`;
                //             a.href = url;
                //             a.download = filename;
                //             document.body.appendChild(a);
                //             a.click();
                //             a.remove();
                //             setTimeout(()=> URL.revokeObjectURL(url), 1000);
                //         }, 'image/png');
                //     }catch(e){
                //         console.error(e);
                //         // ensure buttons restored on error
                //         saveBtn.style.display = prevSaveDisplay;
                //         if (printBtn && prevPrintDisplay !== null) printBtn.style.display = prevPrintDisplay;
                //         alert('Failed to save image: ' + (e.message || e));
                //     }
                // });
            })();
        </script>
    @endpush
@endonce



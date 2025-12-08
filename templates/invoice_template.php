<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice <?= htmlspecialchars($invoice['order_id'] ?? '') ?></title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; position: relative; }
        .page { position: relative; z-index: 1; }
        .watermark { position: fixed; left: 50%; top: 50%; width: 80%; height: auto; max-width: 800px; transform: translate(-50%, -50%); opacity: 0.15; z-index: 0; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .company { text-align: right; }
        .company .code { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .total-row td { border-top: 2px solid #000; font-weight: bold; }
        .meta { margin-bottom: 10px; }
    </style>
</head>
<body>

<img class="watermark" src="/../img/misc/logo_intecgib.png" alt="logo">

<div class="page">
    <div class="header">
        <div class="logo" style="flex:0 0 auto;">
            <img src="/../img/misc/logo_intecgib.png" alt="IntecGIB" style="height:60px;">
        </div>
        <div class="company" style="flex:1 1 auto;">
            <div><strong>INTEC AUTOMATION SOLUTIONS LIMITED</strong></div>
            <div>Company Code: <span class="code">56070</span></div>
        </div>
    </div>

    <div class="meta">
        <div><strong>Invoice #: </strong><?= htmlspecialchars($invoice['order_id'] ?? '') ?></div>
        <div><strong>Date: </strong><?= htmlspecialchars($invoice['date'] ?? date('Y-m-d')) ?></div>
        <div><strong>Bill To:</strong> <?= htmlspecialchars($invoice['customer']['name'] ?? '') ?></div>
        <?php if (!empty($invoice['customer']['email'])): ?><div><strong>Email:</strong> <?= htmlspecialchars($invoice['customer']['email']) ?></div><?php endif; ?>
        <?php if (!empty($invoice['customer']['phone'])): ?><div><strong>Phone:</strong> <?= htmlspecialchars($invoice['customer']['phone']) ?></div><?php endif; ?>
        <?php if (!empty($invoice['customer']['address'])): ?><div><strong>Address:</strong> <?= htmlspecialchars($invoice['customer']['address']) ?></div><?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Description</th>
                <th style="width:80px;">Qty</th>
                <th style="width:120px;">Unit Price</th>
                <th style="width:120px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($invoice['items']) && is_array($invoice['items'])): ?>
                <?php foreach ($invoice['items'] as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['desc'] ?? '') ?></td>
                    <td class="right"><?= htmlspecialchars($it['qty'] ?? 1) ?></td>
                    <td class="right">£ <?= number_format($it['price'] ?? 0,2,'.',',') ?></td>
                    <td class="right">£ <?= number_format(($it['qty'] ?? 1) * ($it['price'] ?? 0),2,'.',',') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No items</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="right">Total</td>
                <td class="right">£ <?= number_format($invoice['total'] ?? 0,2,'.',',') ?></td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top:20px;">Thank you for your business.</p>
</div>

</body>
</html>

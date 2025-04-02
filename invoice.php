<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/Cart.php';
require_once 'includes/User.php';

requireLogin();

// Check if cart is empty
$cart = new Cart();
if ($cart->getCount() === 0) {
    header("Location: cart.php");
    exit();
}

// Get user details
$userModel = new User();
$user = $userModel->getById($_SESSION['user']['id']);

// Create PDF invoice
require_once 'libs/fpdf/fpdf.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, SITE_NAME . ' - Invoice', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Invoice header
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Invoice Date: ' . date('Y-m-d H:i:s'), 0, 1);
$pdf->Cell(0, 10, 'Customer: ' . $user['name'], 0, 1);
$pdf->Cell(0, 10, 'Email: ' . $user['email'], 0, 1);
$pdf->Ln(10);

// Invoice items
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, 'Item', 1);
$pdf->Cell(30, 10, 'Price', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(30, 10, 'Total', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($cart->getItems() as $item) {
    $pdf->Cell(100, 10, $item['title'], 1);
    $pdf->Cell(30, 10, '$' . number_format($item['price'], 2), 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(30, 10, '$' . number_format($item['price'] * $item['quantity'], 2), 1);
    $pdf->Ln();
}

// Invoice total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(160, 10, 'Total:', 1);
$pdf->Cell(30, 10, '$' . number_format($cart->getTotal(), 2), 1, 0, 'R');

// Output PDF
$pdf->Output('D', 'invoice_' . date('Ymd_His') . '.pdf');

// Clear cart after generating invoice
$cart->clear();
?>
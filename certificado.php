<?php
require 'conexion.php';
require 'fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die("ID de preinscripción no especificado.");
}

$id = intval($_GET['id']);

$query = "
    SELECT u.nombre, u.apellido, u.dni, c.nombre AS curso
    FROM preinscripciones p
    JOIN usuarios u ON p.id_usuario = u.id
    JOIN cursos c ON p.id_curso = c.id
    WHERE p.id = $id
";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("No se encontró la inscripción.");
}

$data = $result->fetch_assoc();

$pdf = new FPDF();
$pdf->AddPage();

// Borde decorativo
$pdf->SetLineWidth(1.5);
$pdf->Rect(10, 10, 190, 270);

// Título
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 20, 'CERTIFICADO DE FINALIZACION DE CURSO', 0, 1, 'C');
$pdf->Ln(10);

// Texto principal
$pdf->SetFont('Arial', '', 14);
$pdf->MultiCell(
    0,
    10,
    utf8_decode("Hace constatar que el alumno " . $data['nombre'] . " " . $data['apellido'] . 
    ", DNI " . $data['dni'] . ", ha FINALIZADO satisfactoriamente el curso: " . $data['curso'] . 
    ".\n\nEl alumno ha cumplido con las capacidades y competencias previstas en el programa de formación.")
);
$pdf->Ln(20);

// Firma y pie
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, '_________________________', 0, 1, 'C');
$pdf->Cell(0, 10, 'Firma y Sello', 0, 1, 'C');

$pdf->Output("I", "certificado_finalizacion.pdf");
?>

<?php

namespace App\Controllers\Export;

use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Controllers\Controller;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\JuntasDirectivas;
use App\Models\Modelos\Reconocimiento;
use App\Models\Modelos\Solicitudes;
use App\Models\Modelos\Representantes;

Class ExportController extends Controller {

    public function expor1t($request, $response) {

        $data = Reconocimiento::select('tb_solicitudes.res_dis',
                                'tb_organizacion.nombre_org',
                                'tb_tipos_orgs.descripcion',
                                'tb_puestos.puesto',
                                'tb_representantes.nombre',
                                'tb_representantes.apellido_pat',
                                'tb_representantes.apellido_mat',
                                'tb_solicitudes.inicio',
                                'tb_solicitudes.fin',
                                'tb_solicitudes.tipo_sol',
                                'tb_docs.urldoc')
                        ->join('tb_detalle_recono', 'tb_reconocimiento.codigo', '=', 'tb_detalle_recono.cod_recon')
                        ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                        ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                        ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                        ->join('tb_representantes', 'tb_solicitudes.codigo', '=', 'tb_representantes.cod_sol')
                        ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
                        ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                        ->where('tb_reconocimiento.codigo', 8)
                        ->where('tb_docs.flag', 1)
                        ->orderBy('tb_solicitudes.codigo', 'DESC')->get();

        $type = "xlsx";
        $employees = $data;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'RESOLUCIÓN DISTRITAL');
        $sheet->setCellValue('B1', 'NOMBRE DE ORGANIZACIÓN');
        $sheet->setCellValue('C1', 'TIPO DE ORGANIZACIÓN');
        $sheet->setCellValue('D1', 'CARGO');
        $sheet->setCellValue('E1', 'NOMBRE');
        $sheet->setCellValue('F1', 'APELLIDO PATERNO');
        $sheet->setCellValue('G1', 'APELLIDO MATERNO');
        $sheet->setCellValue('H1', 'INICIO DE VIGENCIA');
        $sheet->setCellValue('I1', 'FIN DE VIGENCIA');
        $sheet->setCellValue('j1', 'TIPO DE SOLICITUD');
        $row = 3;
        $startRow = -1;
        $previousKey = '';
        foreach ($employees as $empDetails) {

            if ($startRow == -1) {
                $startRow = $row;
                $previousKey = $empDetails['fin'];
            }

            // $sheet->mergeCells("A".($rows+1).":A".($rows+4));
            $sheet->setCellValue('A' . $rows, $empDetails['res_dis']);
            $sheet->setCellValue('B' . $rows, $empDetails['nombre_org']);
            $sheet->setCellValue('C' . $rows, $empDetails['descripcion']);
            $sheet->setCellValue('D' . $rows, $empDetails['puesto']);
            $sheet->setCellValue('E' . $rows, $empDetails['nombre']);
            $sheet->setCellValue('F' . $rows, $empDetails['apellido_pat']);
            $sheet->setCellValue('G' . $rows, $empDetails['apellido_mat']);
            $sheet->setCellValue('H' . $rows, $empDetails['inicio']);
            $sheet->setCellValue('I' . $rows, $empDetails['fin']);
            $sheet->setCellValue('J' . $rows, $empDetails['tipo_sol']);

            $nextKey = isset($employees[$empDetails + 1]) ? $employees[$empDetails + 1][1] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge = 'J' . $startRow . ':J' . $row;
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge);
                $startRow = -1;
            }

            $rows++;
        }
        $excelWriter = new Xlsx($spreadsheet);

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);


        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="file.xlsx"');

        $stream = fopen($tempFile, 'r+');

        return $response->withBody(new \Slim\Http\Stream($stream));
    }

    public function export($request, $response, $args) {

            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $styleArrayHead = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

            $codigo = $args['cod'];
            $data = Reconocimiento::select('tb_solicitudes.res_dis',
                                'tb_organizacion.nombre_org',
                                'tb_organizacion.domicilio_org',
                                'tb_organizacion.num_miem',
                                'tb_organizacion.fecha_constitucion',
                                'tb_tipos_orgs.descripcion',
                                'tb_puestos.puesto',
                                'tb_representantes.nombre',
                                'tb_representantes.dni',
                                'tb_representantes.apellido_pat',
                                'tb_representantes.apellido_mat',
                                'tb_solicitudes.inicio',
                                'tb_solicitudes.num_sol',
                                'tb_solicitudes.fin',
                                'tb_solicitudes.tipo_sol',
                                'tb_reconocimiento.num_rec',
                                'tb_distritos.distrito',
                                'tb_docs.urldoc')
                        ->join('tb_detalle_recono', 'tb_reconocimiento.codigo', '=', 'tb_detalle_recono.cod_recon')
                        ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                        ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                        ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                        ->join('tb_representantes', 'tb_solicitudes.codigo', '=', 'tb_representantes.cod_sol')
                        ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
                        ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                        ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
                        ->where('tb_reconocimiento.codigo', $codigo)
                        ->where('tb_docs.flag', 1)
                        ->orderBy('tb_solicitudes.codigo', 'ASC')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'RESOLUCIÓN DISTRITAL')->getColumnDimension('A')->setWidth(40);
        $sheet->setCellValue('B1', 'NOMBRE DE ORGANIZACIÓN')->getColumnDimension('B')->setWidth(40);
        $sheet->setCellValue('C1', 'DIRECCIÓN DE ORGANIZACIÓN')->getColumnDimension('C')->setWidth(40);
        $sheet->setCellValue('D1', 'NÚMERO DE MIEMBROS')->getColumnDimension('D')->setWidth(20);
        $sheet->setCellValue('E1', 'FECHA DE CONSTITUCIÓN')->getColumnDimension('E')->setWidth(20);
        $sheet->setCellValue('F1', 'TIPO DE ORGANIZACIÓN')->getColumnDimension('F')->setWidth(20);
        $sheet->setCellValue('G1', 'CARGO')->getColumnDimension('G')->setWidth(60);
        $sheet->setCellValue('H1', 'NOMBRE')->getColumnDimension('H')->setWidth(40);
        $sheet->setCellValue('I1', 'APELLIDO PATERNO')->getColumnDimension('I')->setWidth(40);
        $sheet->setCellValue('J1', 'APELLIDO MATERNO')->getColumnDimension('J')->setWidth(40);
        $sheet->setCellValue('K1', 'DNI')->getColumnDimension('K')->setWidth(40);
        $sheet->setCellValue('L1', 'INICIO DE VIGENCIA')->getColumnDimension('L')->setWidth(40);
        $sheet->setCellValue('M1', 'FIN DE VIGENCIA')->getColumnDimension('M')->setWidth(40);
        $sheet->setCellValue('N1', 'TIPO DE SOLICITUD')->getColumnDimension('N')->setWidth(40);

        $spreadsheet->getActiveSheet()->getStyle('A1:N1')->applyFromArray($styleArrayHead);

        $row = 2;
        $startRow = -1;
        $previousKey = '';
        foreach ($data as $index => $value) {
            if ($startRow == -1) {
                $startRow = $row;
                $previousKey = $value['num_sol'];
            }
            $sheet->setCellValue('A' . $row, $value['res_dis']);
            $sheet->setCellValue('B' . $row, $value['nombre_org']);
            $sheet->setCellValue('C' . $row, $value['domicilio_org']);
            $sheet->setCellValue('D' . $row, $value['num_miem']);
            $sheet->setCellValue('E' . $row, $value['fecha_constitucion']);
            $sheet->setCellValue('F' . $row, $value['descripcion']);
            $sheet->setCellValue('G' . $row, $value['puesto']);
            $sheet->setCellValue('H' . $row, $value['nombre']);
            $sheet->setCellValue('I' . $row, $value['apellido_pat']);
            $sheet->setCellValue('J' . $row, $value['apellido_mat']);
            $sheet->setCellValue('K' . $row, $value['dni']);
            $sheet->setCellValue('L' . $row, $value['inicio']);
            $sheet->setCellValue('M' . $row, $value['fin']);
            $sheet->setCellValue('N' . $row, $value['tipo_sol']);

            $spreadsheet->getActiveSheet()->getStyle('G' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('H' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('I' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('J' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('K' . $row)->applyFromArray($styleArray);

            $nextKey = isset($data[$index + 1]) ? $data[$index + 1]['num_sol'] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge1 = 'A' . $startRow . ':A' . $row;
                $cellToMerge2 = 'B' . $startRow . ':B' . $row;
                $cellToMerge3 = 'C' . $startRow . ':C' . $row;
                $cellToMerge4 = 'D' . $startRow . ':D' . $row;
                $cellToMerge5 = 'E' . $startRow . ':E' . $row;
                $cellToMerge6 = 'F' . $startRow . ':F' . $row;
                $cellToMerge7 = 'L' . $startRow . ':L' . $row;
                $cellToMerge8 = 'M' . $startRow . ':M' . $row;
                $cellToMerge9 = 'N' . $startRow . ':N' . $row;

                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge1);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge2);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge3);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge4);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge5);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge6);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge7);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge8);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge9);

                $spreadsheet->getActiveSheet()->getStyle($cellToMerge1)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge2)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge3)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge4)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge5)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge6)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge7)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge8)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge9)->applyFromArray($styleArray);

                $startRow = -1;
            }
            $row++;
        }

        $excelWriter = new Xlsx($spreadsheet);

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);


        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$data[0]->distrito."-".$data[0]->num_rec.'.xlsx"');

        $stream = fopen($tempFile, 'r+');

        return $response->withBody(new \Slim\Http\Stream($stream));
    }

    public function exportok($request, $response) {


        $data = JuntasDirectivas::get();

        $datas = array(
            array(1, 'INV201806001', '1000'),
            array(2, 'INV201806001', '1000'),
            array(3, 'INV201806002', '0'),
            array(4, 'INV201807001', '1000'),
            array(5, 'INV201807001', '1000'),
            array(6, 'INV201807001', '1000'),
            array(7, 'INV201807002', '0'),
            array(8, 'INV201807002', '0'),
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 3;
        $startRow = -1;
        $previousKey = '';
        foreach ($data as $index => $value) {
            if ($startRow == -1) {
                $startRow = $row;
                $previousKey = $value['cod_org'];
            }
            $sheet->setCellValue('A' . $row, $value['codigo']);
            $sheet->setCellValue('B' . $row, $value['cod_org']);
            $sheet->setCellValue('C' . $row, $value['cod_org']);
            $nextKey = isset($data[$index + 1]) ? $data[$index + 1]['cod_org'] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge = 'C' . $startRow . ':C' . $row;
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge);
                $startRow = -1;
            }

            $row++;
        }

        $excelWriter = new Xlsx($spreadsheet);

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);


        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="file.xlsx"');

        $stream = fopen($tempFile, 'r+');

        return $response->withBody(new \Slim\Http\Stream($stream));
    }

    public function ConsolidadoOrganizacionesLima($request, $response, $args) {

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $styleArrayHead = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $data = Solicitudes::select('tb_solicitudes.num_sol',
            'tb_organizacion.nombre_org',
            'tb_organizacion.domicilio_org',
            'tb_organizacion.num_miem',
            'tb_organizacion.fecha_constitucion',
            'tb_organizacion.latitud',
            'tb_organizacion.longitud',
            'tb_tipos_orgs.descripcion',
            'tb_puestos.puesto',
            'tb_representantes.nombre',
            'tb_representantes.dni',
            'tb_representantes.apellido_pat',
            'tb_representantes.apellido_mat',
            'tb_representantes.sexo',
            'tb_representantes.fecha_nacimiento',
            'tb_representantes.departamento',
            'tb_representantes.provincia',
            'tb_representantes.distrito',
            'tb_solicitudes.inicio',
            'tb_solicitudes.num_sol',
            'tb_solicitudes.fin',
            'tb_solicitudes.tipo_sol',
            'tb_distritos.distrito as orgdis',
            'tb_solicitante.casa as casa',
            'tb_resolucion.ruta')
            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
            ->join('tb_representantes', 'tb_solicitudes.codigo', '=', 'tb_representantes.cod_sol')
            ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
            ->join('tb_resolucion', 'tb_solicitudes.codigo', '=', 'tb_resolucion.cod_solicitud')
            ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
            ->where('tb_solicitante.tipo_user', 1)
            ->where('tb_solicitudes.flag', 3)
            ->orderBy('tb_solicitudes.codigo', 'ASC')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'NÚMERO DE SOLICITUD')->getColumnDimension('A')->setWidth(40);
        $sheet->setCellValue('B1', 'RESOLUCIÓN DE RECONOCIMIENTO')->getColumnDimension('B')->setWidth(40);
        $sheet->setCellValue('C1', 'NOMBRE DE ORGANIZACIÓN')->getColumnDimension('C')->setWidth(40);
        $sheet->setCellValue('D1', 'DIRECCIÓN DE ORGANIZACIÓN')->getColumnDimension('D')->setWidth(40);
        $sheet->setCellValue('E1', 'DISTRITO DE ORGANIZACIÓN')->getColumnDimension('E')->setWidth(40);
        $sheet->setCellValue('F1', 'NÚMERO DE MIEMBROS')->getColumnDimension('F')->setWidth(20);
        $sheet->setCellValue('G1', 'FECHA DE CONSTITUCIÓN')->getColumnDimension('G')->setWidth(20);
        $sheet->setCellValue('H1', 'TIPO DE ORGANIZACIÓN')->getColumnDimension('H')->setWidth(20);
        $sheet->setCellValue('I1', 'CARGO')->getColumnDimension('I')->setWidth(60);
        $sheet->setCellValue('J1', 'NOMBRE')->getColumnDimension('J')->setWidth(40);
        $sheet->setCellValue('K1', 'APELLIDO PATERNO')->getColumnDimension('K')->setWidth(40);
        $sheet->setCellValue('L1', 'APELLIDO MATERNO')->getColumnDimension('L')->setWidth(40);
        $sheet->setCellValue('M1', 'SEXO')->getColumnDimension('M')->setWidth(40);
        $sheet->setCellValue('N1', 'FECHA DE NACIMIENTO')->getColumnDimension('N')->setWidth(40);
        $sheet->setCellValue('O1', 'DEPARTAMENTO')->getColumnDimension('O')->setWidth(40);
        $sheet->setCellValue('P1', 'PROVINCI')->getColumnDimension('P')->setWidth(40);
        $sheet->setCellValue('Q1', 'DISTRITO')->getColumnDimension('Q')->setWidth(40);
        $sheet->setCellValue('R1', 'DNI')->getColumnDimension('R')->setWidth(40);
        $sheet->setCellValue('S1', 'INICIO DE VIGENCIA')->getColumnDimension('S')->setWidth(40);
        $sheet->setCellValue('T1', 'FIN DE VIGENCIA')->getColumnDimension('T')->setWidth(40);
        $sheet->setCellValue('U1', 'TIPO DE SOLICITUD')->getColumnDimension('U')->setWidth(40);
        $sheet->setCellValue('V1', 'LONGITUD')->getColumnDimension('V')->setWidth(40);
        $sheet->setCellValue('W1', 'LATITUD')->getColumnDimension('W')->setWidth(40);
        $sheet->setCellValue('X1', 'CASA VECINAL')->getColumnDimension('X')->setWidth(40);
        $spreadsheet->getActiveSheet()->getStyle('A1:X1')->applyFromArray($styleArrayHead);

        $row = 2;
        $startRow = -1;
        $previousKey = '';
        foreach ($data as $index => $value) {
            if ($startRow == -1) {
                $startRow = $row;
                $previousKey = $value['num_sol'];
            }
            $sheet->setCellValue('A' . $row, $value['num_sol']);
            $sheet->setCellValue('B' . $row, 'http://ruos.gpvlima.com/'.$value['ruta']);
            $sheet->setCellValue('C' . $row, $value['nombre_org']);
            $sheet->setCellValue('D' . $row, $value['domicilio_org']);
            $sheet->setCellValue('E' . $row, $value['orgdis']);
            $sheet->setCellValue('F' . $row, $value['num_miem']);
            $sheet->setCellValue('G' . $row, $value['fecha_constitucion']);
            $sheet->setCellValue('H' . $row, $value['descripcion']);
            $sheet->setCellValue('I' . $row, $value['puesto']);
            $sheet->setCellValue('J' . $row, $value['nombre']);
            $sheet->setCellValue('K' . $row, $value['apellido_pat']);
            $sheet->setCellValue('L' . $row, $value['apellido_mat']);
            $sheet->setCellValue('M' . $row, $value['sexo']);
            $sheet->setCellValue('N' . $row, $value['fecha_nacimiento']);
            $sheet->setCellValue('O' . $row, $value['departamento']);
            $sheet->setCellValue('P' . $row, $value['provincia']);
            $sheet->setCellValue('Q' . $row, $value['distrito']);
            $sheet->setCellValue('R' . $row, $value['dni']);
            $sheet->setCellValue('S' . $row, $value['inicio']);
            $sheet->setCellValue('T' . $row, $value['fin']);
            $sheet->setCellValue('U' . $row, $value['tipo_sol']);
            $sheet->setCellValue('V' . $row, $value['longitud']);
            $sheet->setCellValue('W' . $row, $value['latitud']);
            $sheet->setCellValue('X' . $row, $value['casa']);

            $spreadsheet->getActiveSheet()->getStyle('H' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('I' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('J' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('K' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('L' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('M' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('N' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('O' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('P' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('R' . $row)->applyFromArray($styleArray);
            $nextKey = isset($data[$index + 1]) ? $data[$index + 1]['num_sol'] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge1 = 'A' . $startRow . ':A' . $row;
                $cellToMerge2 = 'B' . $startRow . ':B' . $row;
                $cellToMerge3 = 'C' . $startRow . ':C' . $row;
                $cellToMerge4 = 'D' . $startRow . ':D' . $row;
                $cellToMerge5 = 'E' . $startRow . ':E' . $row;
                $cellToMerge6 = 'F' . $startRow . ':F' . $row;
                $cellToMerge7 = 'G' . $startRow . ':G' . $row;
                $cellToMerge8 = 'H' . $startRow . ':H' . $row;
                $cellToMerge13 = 'Q' . $startRow . ':Q' . $row;
                $cellToMerge14 = 'R' . $startRow . ':R' . $row;
                $cellToMerge15 = 'S' . $startRow . ':S' . $row;
                $cellToMerge16 = 'T' . $startRow . ':T' . $row;
                $cellToMerge17 = 'U' . $startRow . ':U' . $row;
                $cellToMerge18 = 'V' . $startRow . ':V' . $row;
                $cellToMerge19 = 'W' . $startRow . ':W' . $row;
                $cellToMerge20 = 'X' . $startRow . ':X' . $row;
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge1);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge2);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge3);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge4);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge5);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge6);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge7);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge8);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge15);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge16);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge17);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge18);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge19);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge20);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge1)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge2)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge3)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge4)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge5)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge6)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge7)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge8)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge13)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge14)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge15)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge16)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge17)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge18)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge19)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge20)->applyFromArray($styleArray);
                $startRow = -1;
            }
            $row++;
        }
        $excelWriter = new Xlsx($spreadsheet);
        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);
        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="ConsolidadoLima.xlsx"');
        $stream = fopen($tempFile, 'r+');
        return $response->withBody(new \Slim\Http\Stream($stream));
    }

    public function ConsolidadoOrganizacionesDistritales($request, $response, $args) {

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $styleArrayHead = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $from = date('2022-01-01');
        $to = date('2023-12-31');

        $data = Solicitudes::select('tb_solicitudes.num_sol',
            'tb_organizacion.nombre_org',
            'tb_organizacion.domicilio_org',
            'tb_organizacion.num_miem',
            'tb_organizacion.fecha_constitucion',
            'tb_organizacion.latitud',
            'tb_organizacion.longitud',
            'tb_tipos_orgs.descripcion',
            'tb_puestos.puesto',
            'tb_representantes.nombre',
            'tb_representantes.dni',
            'tb_representantes.apellido_pat',
            'tb_representantes.apellido_mat',
            'tb_representantes.sexo',
            'tb_representantes.fecha_nacimiento',
            'tb_representantes.departamento',
            'tb_representantes.provincia',
            'tb_representantes.distrito',
            'tb_solicitudes.inicio',
            'tb_solicitudes.num_sol',
            'tb_solicitudes.fin',
            'tb_solicitudes.tipo_sol',
            'tb_distritos.distrito as orgdis',
            'tb_docs.urldoc')
            ->join('tb_solicitante', 'tb_solicitudes.cod_usuario', '=', 'tb_solicitante.codigo')
            ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
            ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
            ->join('tb_representantes', 'tb_solicitudes.codigo', '=', 'tb_representantes.cod_sol')
            ->join('tb_puestos', 'tb_representantes.cod_puesto', '=', 'tb_puestos.codigo')
            ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
            ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
            ->where('tb_solicitante.tipo_user', 2)
            ->where('tb_solicitudes.flag', 3)
            ->whereBetween('tb_solicitudes.freg', [$from, $to])
            ->where('tb_docs.flag', 1)
            ->orderBy('tb_solicitudes.codigo', 'ASC')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'NÚMERO DE SOLICITUD')->getColumnDimension('A')->setWidth(40);
        $sheet->setCellValue('B1', 'RESOLUCIÓN DE RECONOCIMIENTO')->getColumnDimension('B')->setWidth(40);
        $sheet->setCellValue('C1', 'NOMBRE DE ORGANIZACIÓN')->getColumnDimension('C')->setWidth(40);
        $sheet->setCellValue('D1', 'DIRECCIÓN DE ORGANIZACIÓN')->getColumnDimension('D')->setWidth(40);
        $sheet->setCellValue('E1', 'DISTRITO DE ORGANIZACIÓN')->getColumnDimension('E')->setWidth(40);
        $sheet->setCellValue('F1', 'NÚMERO DE MIEMBROS')->getColumnDimension('F')->setWidth(20);
        $sheet->setCellValue('G1', 'FECHA DE CONSTITUCIÓN')->getColumnDimension('G')->setWidth(20);
        $sheet->setCellValue('H1', 'TIPO DE ORGANIZACIÓN')->getColumnDimension('H')->setWidth(20);
        $sheet->setCellValue('I1', 'CARGO')->getColumnDimension('I')->setWidth(60);
        $sheet->setCellValue('J1', 'NOMBRE')->getColumnDimension('J')->setWidth(40);
        $sheet->setCellValue('K1', 'APELLIDO PATERNO')->getColumnDimension('K')->setWidth(40);
        $sheet->setCellValue('L1', 'APELLIDO MATERNO')->getColumnDimension('L')->setWidth(40);
        $sheet->setCellValue('M1', 'SEXO')->getColumnDimension('M')->setWidth(40);
        $sheet->setCellValue('N1', 'FECHA DE NACIMIENTO')->getColumnDimension('N')->setWidth(40);
        $sheet->setCellValue('O1', 'DEPARTAMENTO')->getColumnDimension('O')->setWidth(40);
        $sheet->setCellValue('P1', 'PROVINCI')->getColumnDimension('P')->setWidth(40);
        $sheet->setCellValue('Q1', 'DISTRITO')->getColumnDimension('Q')->setWidth(40);
        $sheet->setCellValue('R1', 'DNI')->getColumnDimension('R')->setWidth(40);
        $sheet->setCellValue('S1', 'INICIO DE VIGENCIA')->getColumnDimension('S')->setWidth(40);
        $sheet->setCellValue('T1', 'FIN DE VIGENCIA')->getColumnDimension('T')->setWidth(40);
        $sheet->setCellValue('U1', 'TIPO DE SOLICITUD')->getColumnDimension('U')->setWidth(40);
        $sheet->setCellValue('V1', 'LONGITUD')->getColumnDimension('V')->setWidth(40);
        $sheet->setCellValue('W1', 'LATITUD')->getColumnDimension('W')->setWidth(40);

        $spreadsheet->getActiveSheet()->getStyle('A1:W1')->applyFromArray($styleArrayHead);

        $row = 2;
        $startRow = -1;
        $previousKey = '';
        foreach ($data as $index => $value) {
            if ($startRow == -1) {
                $startRow = $row;
                $previousKey = $value['num_sol'];
            }
            $sheet->setCellValue('A' . $row, $value['num_sol']);
            $sheet->setCellValue('B' . $row, 'http://ruos.gpvlima.com/'.$value['urldoc']);
            $sheet->setCellValue('C' . $row, $value['nombre_org']);
            $sheet->setCellValue('D' . $row, $value['domicilio_org']);
            $sheet->setCellValue('E' . $row, $value['orgdis']);
            $sheet->setCellValue('F' . $row, $value['num_miem']);
            $sheet->setCellValue('G' . $row, $value['fecha_constitucion']);
            $sheet->setCellValue('H' . $row, $value['descripcion']);
            $sheet->setCellValue('I' . $row, $value['puesto']);
            $sheet->setCellValue('J' . $row, $value['nombre']);
            $sheet->setCellValue('K' . $row, $value['apellido_pat']);
            $sheet->setCellValue('L' . $row, $value['apellido_mat']);
            $sheet->setCellValue('M' . $row, $value['sexo']);
            $sheet->setCellValue('N' . $row, $value['fecha_nacimiento']);
            $sheet->setCellValue('O' . $row, $value['departamento']);
            $sheet->setCellValue('P' . $row, $value['provincia']);
            $sheet->setCellValue('Q' . $row, $value['distrito']);
            $sheet->setCellValue('R' . $row, $value['dni']);
            $sheet->setCellValue('S' . $row, $value['inicio']);
            $sheet->setCellValue('T' . $row, $value['fin']);
            $sheet->setCellValue('U' . $row, $value['tipo_sol']);
            $sheet->setCellValue('V' . $row, $value['longitud']);
            $sheet->setCellValue('W' . $row, $value['latitud']);

            $spreadsheet->getActiveSheet()->getStyle('H' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('I' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('J' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('K' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('L' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('M' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('N' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('O' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('P' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('R' . $row)->applyFromArray($styleArray);
            $nextKey = isset($data[$index + 1]) ? $data[$index + 1]['num_sol'] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge1 = 'A' . $startRow . ':A' . $row;
                $cellToMerge2 = 'B' . $startRow . ':B' . $row;
                $cellToMerge3 = 'C' . $startRow . ':C' . $row;
                $cellToMerge4 = 'D' . $startRow . ':D' . $row;
                $cellToMerge5 = 'E' . $startRow . ':E' . $row;
                $cellToMerge6 = 'F' . $startRow . ':F' . $row;
                $cellToMerge7 = 'G' . $startRow . ':G' . $row;
                $cellToMerge8 = 'H' . $startRow . ':H' . $row;
                $cellToMerge13 = 'Q' . $startRow . ':Q' . $row;
                $cellToMerge14 = 'R' . $startRow . ':R' . $row;
                $cellToMerge15 = 'S' . $startRow . ':S' . $row;
                $cellToMerge16 = 'T' . $startRow . ':T' . $row;
                $cellToMerge17 = 'U' . $startRow . ':U' . $row;
                $cellToMerge18 = 'V' . $startRow . ':V' . $row;
                $cellToMerge19 = 'W' . $startRow . ':W' . $row;
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge1);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge2);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge3);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge4);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge5);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge6);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge7);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge8);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge15);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge16);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge17);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge18);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge19);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge1)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge2)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge3)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge4)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge5)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge6)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge7)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge8)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge13)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge14)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge15)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge16)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge17)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge18)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle($cellToMerge19)->applyFromArray($styleArray);
                $startRow = -1;
            }
            $row++;
        }
        $excelWriter = new Xlsx($spreadsheet);
        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);
        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="ConsolidadoDistrital.xlsx"');
        $stream = fopen($tempFile, 'r+');
        return $response->withBody(new \Slim\Http\Stream($stream));
    }
}

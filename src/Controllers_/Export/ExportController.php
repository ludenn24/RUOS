<?php

namespace App\Controllers\Export;

use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Controllers\Controller;
use App\Models\Modelos\Organizaciones;
use App\Models\Modelos\JuntasDirectivas;
use App\Models\Modelos\Reconocimiento;

Class ExportController extends Controller {

    public function expor1t($request, $response) {

        $data = Reconocimiento::select('tb_solicitudes.res_dis',
                                'tb_organizacion.nombre_org',
                                'tb_tipos_orgs.descripcion',
                                'tb_puestos.puesto',
                                'tb_junta_directiva.nombre',
                                'tb_junta_directiva.apellido_pat',
                                'tb_junta_directiva.apellido_mat',
                                'tb_solicitudes.inicio',
                                'tb_solicitudes.fin',
                                'tb_solicitudes.tipo_sol',
                                'tb_docs.urldoc')
                        ->join('tb_detalle_recono', 'tb_reconocimiento.codigo', '=', 'tb_detalle_recono.cod_recon')
                        ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                        ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                        ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                        ->join('tb_junta_directiva', 'tb_organizacion.codigo', '=', 'tb_junta_directiva.cod_org')
                        ->join('tb_puestos', 'tb_junta_directiva.cod_puesto', '=', 'tb_puestos.codigo')
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
            $codigo = $args['cod'];
            $data = Reconocimiento::select('tb_solicitudes.res_dis',
                                'tb_organizacion.nombre_org',
                                'tb_tipos_orgs.descripcion',
                                'tb_puestos.puesto',
                                'tb_junta_directiva.nombre',
                                'tb_junta_directiva.dni',
                                'tb_junta_directiva.apellido_pat',
                                'tb_junta_directiva.apellido_mat',
                                'tb_solicitudes.inicio',
                                'tb_solicitudes.num_sol',
                                'tb_solicitudes.fin',
                                'tb_solicitudes.tipo_sol',
                                'tb_distritos.distrito',
                                'tb_docs.urldoc')
                        ->join('tb_detalle_recono', 'tb_reconocimiento.codigo', '=', 'tb_detalle_recono.cod_recon')
                        ->join('tb_solicitudes', 'tb_detalle_recono.cod_solicitud', '=', 'tb_solicitudes.codigo')
                        ->join('tb_organizacion', 'tb_solicitudes.cod_org', '=', 'tb_organizacion.codigo')
                        ->join('tb_tipos_orgs', 'tb_organizacion.tipo_org', '=', 'tb_tipos_orgs.codigo')
                        ->join('tb_junta_directiva', 'tb_organizacion.codigo', '=', 'tb_junta_directiva.cod_org')
                        ->join('tb_puestos', 'tb_junta_directiva.cod_puesto', '=', 'tb_puestos.codigo')
                        ->join('tb_docs', 'tb_solicitudes.codigo', '=', 'tb_docs.idsol')
                        ->join('tb_distritos', 'tb_organizacion.distrito', '=', 'tb_distritos.idDist')
                        ->where('tb_reconocimiento.codigo', $codigo)
                        ->where('tb_docs.flag', 1)
                        ->orderBy('tb_solicitudes.codigo', 'ASC')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'RESOLUCIÓN DISTRITAL');
        $sheet->setCellValue('B1', 'NOMBRE DE ORGANIZACIÓN');
        $sheet->setCellValue('C1', 'TIPO DE ORGANIZACIÓN');
        $sheet->setCellValue('D1', 'CARGO');
        $sheet->setCellValue('E1', 'NOMBRE');
        $sheet->setCellValue('F1', 'APELLIDO PATERNO');
        $sheet->setCellValue('G1', 'APELLIDO MATERNO');
        $sheet->setCellValue('H1', 'DNI');
        $sheet->setCellValue('I1', 'INICIO DE VIGENCIA');
        $sheet->setCellValue('J1', 'FIN DE VIGENCIA');
        $sheet->setCellValue('K1', 'TIPO DE SOLICITUD');
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
            $sheet->setCellValue('C' . $row, $value['descripcion']);
            $sheet->setCellValue('D' . $row, $value['puesto']);
            $sheet->setCellValue('E' . $row, $value['nombre']);
            $sheet->setCellValue('F' . $row, $value['apellido_pat']);
            $sheet->setCellValue('G' . $row, $value['apellido_mat']);
            $sheet->setCellValue('H' . $row, $value['dni']);
            $sheet->setCellValue('I' . $row, $value['inicio']);
            $sheet->setCellValue('J' . $row, $value['fin']);
            $sheet->setCellValue('K' . $row, $value['tipo_sol']);

            $nextKey = isset($data[$index + 1]) ? $data[$index + 1]['num_sol'] : null;

            if ($row >= $startRow && (($previousKey <> $nextKey) || ($nextKey == null))) {
                $cellToMerge1 = 'A' . $startRow . ':A' . $row;
                $cellToMerge2 = 'B' . $startRow . ':B' . $row;
                $cellToMerge3 = 'C' . $startRow . ':C' . $row;
                $cellToMerge4 = 'I' . $startRow . ':I' . $row;
                $cellToMerge5 = 'J' . $startRow . ':J' . $row;
                $cellToMerge6 = 'K' . $startRow . ':K' . $row;
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge1);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge2);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge3);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge4);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge5);
                $spreadsheet->getActiveSheet()->mergeCells($cellToMerge6);
                $startRow = -1;
            }
            $row++;
        }

        $excelWriter = new Xlsx($spreadsheet);

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
        $excelWriter->save($tempFile);


        $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$data[0]->distrito.'.xlsx"');

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

}

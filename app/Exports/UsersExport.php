<?php

namespace App\Exports;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\Style;

class UsersExport
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function exportToExcel($filename = 'usuarios.xlsx')
    {
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile(storage_path('app/temp/' . $filename));

        // Crear estilo para los encabezados
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(12)
            ->setShouldWrapText(false)
            ->build();

        // Escribir encabezados
        $headers = ['ID', 'Nombre', 'Email', 'Rol', 'Creado'];
        $headerRow = WriterEntityFactory::createRowFromArray($headers, $headerStyle);
        $writer->addRow($headerRow);

        // Escribir datos
        foreach ($this->users as $user) {
            $rowData = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->created_at->format('d/m/Y H:i:s'),
            ];
            $row = WriterEntityFactory::createRowFromArray($rowData);
            $writer->addRow($row);
        }

        $writer->close();

        return storage_path('app/temp/' . $filename);
    }

    public function exportToCsv($filename = 'usuarios.csv')
    {
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToFile(storage_path('app/temp/' . $filename));

        // Escribir encabezados
        $headers = ['ID', 'Nombre', 'Email', 'Rol', 'Creado'];
        $headerRow = WriterEntityFactory::createRowFromArray($headers);
        $writer->addRow($headerRow);

        // Escribir datos
        foreach ($this->users as $user) {
            $rowData = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->created_at->format('d/m/Y H:i:s'),
            ];
            $row = WriterEntityFactory::createRowFromArray($rowData);
            $writer->addRow($row);
        }

        $writer->close();

        return storage_path('app/temp/' . $filename);
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommitmentsReport implements FromCollection, WithHeadings
{

    use Exportable;

    private $data;
    private $headers;


    public function __construct($data, $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function collection()
    {
        // Filter out the 'id' field
        return collect($this->data)->map(function($item) {

            if (!$item->done){
                $item->done = "No";
            }

            if (!$item->done_date){
                $item->done_date = "No realizado aún";
            }

            return [
                'user_name' => $item->user_name,
                'dependency_name' => $item->dependency_name,
                'competence_name' => $item->competence_name,
                'training_name' => $item->training_name,
                'due_date' => $item->due_date,
                'done' => $item->done,
                'amount_of_files' => $item->amount_of_files,
                'done_date' => $item->done_date,
            ];
        });
    }

    public function headings(): array
    {
        return $this->headers;
    }


}

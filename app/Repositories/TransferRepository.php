<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository{
    public function create(array $data){
        return Transfer::create($data);
    }

    public function findOrFail(int $id){
        return Transfer::with(['fromAccount', 'toAccount', 'transactions'])->findOrFail($id);
    }
    
}
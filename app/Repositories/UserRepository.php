<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{
    public function find(int $id){
        return User::find($id);
    }

    public function findOrFail(int $id){
        return User::findOrFail($id);
    }

    public function findByEmail(string $email){
        return User::where('email', $email)->first();
    }

    public function create(array $data){
        return User::create($data);
    }

    public function update(int $id, array $data){
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }
}
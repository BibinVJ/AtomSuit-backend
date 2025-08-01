<?php

namespace App\Repositories;

use App\Models\Enquiry;

class EnquiryRepository
{
    public function store($data): Enquiry
    {
        return Enquiry::create([
            'name' => $data['fullName'],
            'email' => $data['email'],
            'contact_number' => $data['contactNumber'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);
    }
}

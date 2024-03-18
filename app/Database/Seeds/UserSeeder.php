<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
       $data = array(
            'name' =>   'Admin',
            'email' =>  'info@mediax.co.za',
            'username' =>   'mxadmin',
            'password' =>   password_hash('bertrooT1973!', PASSWORD_BCRYPT),


       );
       $this->db->table('users')->insert($data);
    }
}

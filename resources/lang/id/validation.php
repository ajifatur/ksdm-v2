<?php

return [
    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut ini berisi standar pesan kesalahan yang digunakan oleh
    | kelas validasi. Beberapa aturan mempunyai banyak versi seperti aturan 'size'.
    | Jangan ragu untuk mengoptimalkan setiap pesan yang ada di sini.
    |
    */

    'accepted'        => 'Harus diterima.',
    'active_url'      => 'Bukan URL yang valid.',
    'after'           => 'Harus berisi tanggal setelah :date.',
    'after_or_equal'  => 'Harus berisi tanggal setelah atau sama dengan :date.',
    'alpha'           => 'Hanya boleh berisi huruf.',
    'alpha_dash'      => 'Hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'       => 'Hanya boleh berisi huruf dan angka.',
    'array'           => 'Harus berisi sebuah array.',
    'before'          => 'Harus berisi tanggal sebelum :date.',
    'before_or_equal' => 'Harus berisi tanggal sebelum atau sama dengan :date.',
    'between'         => [
        'numeric' => 'Harus bernilai antara :min sampai :max.',
        'file'    => 'Harus berukuran antara :min sampai :max kilobita.',
        'string'  => 'Harus berisi antara :min sampai :max karakter.',
        'array'   => 'Harus memiliki :min sampai :max anggota.',
    ],
    'boolean'        => 'Harus bernilai true atau false',
    'confirmed'      => 'Konfirmasi tidak cocok.',
    'date'           => 'Bukan tanggal yang valid.',
    'date_equals'    => 'Harus berisi tanggal yang sama dengan :date.',
    'date_format'    => 'Tidak cocok dengan format :format.',
    'different'      => 'Dengan :other harus berbeda.',
    'digits'         => 'Harus terdiri dari :digits angka.',
    'digits_between' => 'Harus terdiri dari :min sampai :max angka.',
    'dimensions'     => 'Tidak memiliki dimensi gambar yang valid.',
    'distinct'       => 'Memiliki nilai yang duplikat.',
    'email'          => 'Harus berupa alamat surel yang valid.',
    'ends_with'      => 'Harus diakhiri salah satu dari berikut: :values',
    'exists'         => 'Yang dipilih tidak valid.',
    'file'           => 'Harus berupa sebuah berkas.',
    'filled'         => 'Harus memiliki nilai.',
    'gt'             => [
        'numeric' => 'Harus bernilai lebih besar dari :value.',
        'file'    => 'Harus berukuran lebih besar dari :value kilobita.',
        'string'  => 'Harus berisi lebih besar dari :value karakter.',
        'array'   => 'Harus memiliki lebih dari :value anggota.',
    ],
    'gte' => [
        'numeric' => 'Harus bernilai lebih besar dari atau sama dengan :value.',
        'file'    => 'Harus berukuran lebih besar dari atau sama dengan :value kilobita.',
        'string'  => 'Harus berisi lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Harus terdiri dari :value anggota atau lebih.',
    ],
    'image'    => 'Harus berupa gambar.',
    'in'       => 'Yang dipilih tidak valid.',
    'in_array' => 'Tidak ada di dalam :other.',
    'integer'  => 'Harus berupa bilangan bulat.',
    'ip'       => 'Harus berupa alamat IP yang valid.',
    'ipv4'     => 'Harus berupa alamat IPv4 yang valid.',
    'ipv6'     => 'Harus berupa alamat IPv6 yang valid.',
    'json'     => 'Harus berupa JSON string yang valid.',
    'lt'       => [
        'numeric' => 'Harus bernilai kurang dari :value.',
        'file'    => 'Harus berukuran kurang dari :value kilobita.',
        'string'  => 'Harus berisi kurang dari :value karakter.',
        'array'   => 'Harus memiliki kurang dari :value anggota.',
    ],
    'lte' => [
        'numeric' => 'Harus bernilai kurang dari atau sama dengan :value.',
        'file'    => 'Harus berukuran kurang dari atau sama dengan :value kilobita.',
        'string'  => 'Harus berisi kurang dari atau sama dengan :value karakter.',
        'array'   => 'Harus tidak lebih dari :value anggota.',
    ],
    'max' => [
        'numeric' => 'Maskimal bernilai :max.',
        'file'    => 'Maksimal berukuran :max kilobita.',
        'string'  => 'Maskimal berisi :max karakter.',
        'array'   => 'Maksimal terdiri dari :max anggota.',
    ],
    'mimes'     => 'Harus berupa berkas berjenis: :values.',
    'mimetypes' => 'Harus berupa berkas berjenis: :values.',
    'min'       => [
        'numeric' => 'Minimal bernilai :min.',
        'file'    => 'Minimal berukuran :min kilobita.',
        'string'  => 'Minimal berisi :min karakter.',
        'array'   => 'Minimal terdiri dari :min anggota.',
    ],
    'not_in'               => 'Yang dipilih tidak valid.',
    'not_regex'            => 'Format tidak valid.',
    'numeric'              => 'Harus berupa angka.',
    'password'             => 'Kata sandi salah.',
    'present'              => 'Wajib ada.',
    'regex'                => 'Format tidak valid.',
    'required'             => 'Wajib diisi.',
    'required_if'          => 'Wajib diisi bila :other adalah :value.',
    'required_unless'      => 'Wajib diisi kecuali :other memiliki nilai :values.',
    'required_with'        => 'Wajib diisi bila terdapat :values.',
    'required_with_all'    => 'Wajib diisi bila terdapat :values.',
    'required_without'     => 'Wajib diisi bila tidak terdapat :values.',
    'required_without_all' => 'Wajib diisi bila sama sekali tidak terdapat :values.',
    'same'                 => 'Dengan :other harus sama.',
    'size'                 => [
        'numeric' => 'Harus berukuran :size.',
        'file'    => 'Harus berukuran :size kilobyte.',
        'string'  => 'Harus berukuran :size karakter.',
        'array'   => 'Harus mengandung :size anggota.',
    ],
    'starts_with' => 'Harus diawali salah satu dari berikut: :values',
    'string'      => 'Harus berupa string.',
    'timezone'    => 'Harus berisi zona waktu yang valid.',
    'unique'      => 'Sudah ada sebelumnya.',
    'uploaded'    => 'Gagal diunggah.',
    'url'         => 'Format tidak valid.',
    'uuid'        => 'Harus merupakan UUID yang valid.',

    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi Kustom
    |---------------------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi untuk atribut sesuai keinginan dengan menggunakan 
    | konvensi "attribute.rule" dalam penamaan barisnya. Hal ini mempercepat dalam menentukan
    | baris bahasa kustom yang spesifik untuk aturan atribut yang diberikan.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |---------------------------------------------------------------------------------------
    | Kustom Validasi Atribut
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar 'placeholder' atribut dengan sesuatu yang
    | lebih mudah dimengerti oleh pembaca seperti "Alamat Surel" daripada "surel" saja.
    | Hal ini membantu kita dalam membuat pesan menjadi lebih ekspresif.
    |
    */

    'attributes' => [
    ],
];
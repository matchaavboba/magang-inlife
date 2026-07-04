<?php

namespace Database\Seeders;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\EventLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Create Roles (idempotent) =====
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);

        // ===== Create Users (idempotent) =====
        $admin = User::firstOrCreate(
            ['email' => 'admin@telkomsel.co.id'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('admin');

        $staff = User::firstOrCreate(
            ['email' => 'staff@telkomsel.co.id'],
            ['name' => 'Staff Inventaris', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $staff->assignRole('staff');

        $manager = User::firstOrCreate(
            ['email' => 'manager@telkomsel.co.id'],
            ['name' => 'Manager Operasional', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $manager->assignRole('manager');

        // ===== Create Categories =====
        $categories = [
            ['name' => 'Elektronik', 'description' => 'Perangkat elektronik kantor seperti laptop, monitor, printer'],
            ['name' => 'Furnitur', 'description' => 'Perabotan kantor seperti meja, kursi, lemari'],
            ['name' => 'Alat Tulis', 'description' => 'Alat tulis kantor dan perlengkapan stationery'],
            ['name' => 'Jaringan', 'description' => 'Perangkat jaringan seperti router, switch, kabel'],
            ['name' => 'Kendaraan', 'description' => 'Kendaraan operasional kantor'],
            ['name' => 'Peralatan', 'description' => 'Peralatan pendukung operasional kantor'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // ===== Create Products =====
        $products = [
            ['kode_barang' => 'BRG-0001', 'nama_barang' => 'Laptop Dell Latitude 5540', 'category_id' => 1, 'stok' => 25, 'min_stok' => 5, 'lokasi' => 'Gudang A - Rak 1', 'kondisi' => 'baik', 'deskripsi' => 'Laptop bisnis Dell Latitude 5540 i7 16GB RAM'],
            ['kode_barang' => 'BRG-0002', 'nama_barang' => 'Monitor LG 27" 4K', 'category_id' => 1, 'stok' => 15, 'min_stok' => 3, 'lokasi' => 'Gudang A - Rak 2', 'kondisi' => 'baik', 'deskripsi' => 'Monitor LG UltraFine 27 inch 4K IPS'],
            ['kode_barang' => 'BRG-0003', 'nama_barang' => 'Printer HP LaserJet Pro', 'category_id' => 1, 'stok' => 8, 'min_stok' => 2, 'lokasi' => 'Gudang A - Rak 3', 'kondisi' => 'baik', 'deskripsi' => 'Printer laser multifungsi HP'],
            ['kode_barang' => 'BRG-0004', 'nama_barang' => 'Keyboard Logitech MX Keys', 'category_id' => 1, 'stok' => 30, 'min_stok' => 10, 'lokasi' => 'Gudang A - Rak 4', 'kondisi' => 'baik', 'deskripsi' => 'Keyboard wireless premium Logitech'],
            ['kode_barang' => 'BRG-0005', 'nama_barang' => 'Mouse Logitech MX Master 3', 'category_id' => 1, 'stok' => 28, 'min_stok' => 10, 'lokasi' => 'Gudang A - Rak 4', 'kondisi' => 'baik', 'deskripsi' => 'Mouse wireless ergonomis Logitech'],
            ['kode_barang' => 'BRG-0006', 'nama_barang' => 'Meja Kerja Standing Desk', 'category_id' => 2, 'stok' => 12, 'min_stok' => 3, 'lokasi' => 'Gudang B - Area 1', 'kondisi' => 'baik', 'deskripsi' => 'Meja kerja electric standing desk'],
            ['kode_barang' => 'BRG-0007', 'nama_barang' => 'Kursi Ergonomis Herman Miller', 'category_id' => 2, 'stok' => 20, 'min_stok' => 5, 'lokasi' => 'Gudang B - Area 2', 'kondisi' => 'baik', 'deskripsi' => 'Kursi ergonomis premium Herman Miller Aeron'],
            ['kode_barang' => 'BRG-0008', 'nama_barang' => 'Lemari Arsip Besi', 'category_id' => 2, 'stok' => 6, 'min_stok' => 2, 'lokasi' => 'Gudang B - Area 3', 'kondisi' => 'baik', 'deskripsi' => 'Lemari arsip besi 4 laci dengan kunci'],
            ['kode_barang' => 'BRG-0009', 'nama_barang' => 'Kertas HVS A4 (rim)', 'category_id' => 3, 'stok' => 3, 'min_stok' => 20, 'lokasi' => 'Gudang C - Rak 1', 'kondisi' => 'baik', 'deskripsi' => 'Kertas HVS A4 80gsm per rim'],
            ['kode_barang' => 'BRG-0010', 'nama_barang' => 'Tinta Printer HP 680', 'category_id' => 3, 'stok' => 4, 'min_stok' => 10, 'lokasi' => 'Gudang C - Rak 2', 'kondisi' => 'baik', 'deskripsi' => 'Cartridge tinta original HP 680 Black'],
            ['kode_barang' => 'BRG-0011', 'nama_barang' => 'Router Cisco Meraki MR46', 'category_id' => 4, 'stok' => 10, 'min_stok' => 2, 'lokasi' => 'Server Room', 'kondisi' => 'baik', 'deskripsi' => 'Wireless access point enterprise Cisco Meraki'],
            ['kode_barang' => 'BRG-0012', 'nama_barang' => 'Switch Cisco Catalyst 9200', 'category_id' => 4, 'stok' => 5, 'min_stok' => 1, 'lokasi' => 'Server Room', 'kondisi' => 'baik', 'deskripsi' => 'Managed switch 48 port gigabit'],
            ['kode_barang' => 'BRG-0013', 'nama_barang' => 'Kabel UTP Cat6 (box)', 'category_id' => 4, 'stok' => 2, 'min_stok' => 3, 'lokasi' => 'Gudang C - Rak 3', 'kondisi' => 'baik', 'deskripsi' => 'Kabel jaringan UTP Cat6 305m per box'],
            ['kode_barang' => 'BRG-0014', 'nama_barang' => 'Proyektor Epson EB-X51', 'category_id' => 1, 'stok' => 4, 'min_stok' => 1, 'lokasi' => 'Gudang A - Rak 5', 'kondisi' => 'baik', 'deskripsi' => 'Proyektor XGA 3800 lumens Epson'],
            ['kode_barang' => 'BRG-0015', 'nama_barang' => 'Whiteboard Magnetic 120x240', 'category_id' => 6, 'stok' => 7, 'min_stok' => 2, 'lokasi' => 'Gudang B - Area 4', 'kondisi' => 'baik', 'deskripsi' => 'Whiteboard magnetic premium ukuran besar'],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(['kode_barang' => $prod['kode_barang']], $prod);
        }

        // ===== Create Sample Borrowings =====
        $borrowings = [
            [
                'user_id' => $staff->id,
                'borrower_name' => 'Budi Santoso',
                'tanggal_pinjam' => now()->subDays(10),
                'tanggal_kembali' => now()->addDays(4),
                'status' => 'dipinjam',
                'catatan' => 'Untuk keperluan meeting project Alpha',
                'details' => [
                    ['product_id' => 1, 'qty' => 1, 'kondisi_pinjam' => 'baik'],
                    ['product_id' => 5, 'qty' => 1, 'kondisi_pinjam' => 'baik'],
                ]
            ],
            [
                'user_id' => $staff->id,
                'borrower_name' => 'Siti Rahayu',
                'tanggal_pinjam' => now()->subDays(20),
                'tanggal_kembali' => now()->subDays(5),
                'tanggal_dikembalikan' => now()->subDays(4),
                'status' => 'dikembalikan',
                'catatan' => 'Untuk presentasi client',
                'details' => [
                    ['product_id' => 14, 'qty' => 1, 'kondisi_pinjam' => 'baik', 'kondisi_kembali' => 'baik'],
                ]
            ],
            [
                'user_id' => $admin->id,
                'borrower_name' => 'Ahmad Fauzi',
                'tanggal_pinjam' => now()->subDays(30),
                'tanggal_kembali' => now()->subDays(15),
                'status' => 'dipinjam',
                'catatan' => 'Untuk WFH',
                'details' => [
                    ['product_id' => 1, 'qty' => 2, 'kondisi_pinjam' => 'baik'],
                    ['product_id' => 2, 'qty' => 1, 'kondisi_pinjam' => 'baik'],
                    ['product_id' => 4, 'qty' => 2, 'kondisi_pinjam' => 'baik'],
                ]
            ],
            [
                'user_id' => $staff->id,
                'borrower_name' => 'Dewi Lestari',
                'tanggal_pinjam' => now()->subDays(5),
                'tanggal_kembali' => now()->addDays(9),
                'status' => 'dipinjam',
                'catatan' => 'Training regional Jawa Barat',
                'details' => [
                    ['product_id' => 14, 'qty' => 1, 'kondisi_pinjam' => 'baik'],
                    ['product_id' => 1, 'qty' => 3, 'kondisi_pinjam' => 'baik'],
                ]
            ],
        ];

        foreach ($borrowings as $data) {
            $details = $data['details'];
            unset($data['details']);
            $borrowing = Borrowing::create($data);

            foreach ($details as $detail) {
                $detail['borrowing_id'] = $borrowing->id;
                BorrowingDetail::create($detail);
            }
        }

        // ===== Create Sample Event Logs (for Big Data demo) =====
        $eventTypes = [
            ['event_type' => 'STOCK_UPDATED', 'topic' => 'inventory', 'reference_type' => 'product'],
            ['event_type' => 'BORROWING_CREATED', 'topic' => 'borrowing', 'reference_type' => 'borrowing'],
            ['event_type' => 'LOW_STOCK_ALERT', 'topic' => 'analytics', 'reference_type' => 'product'],
            ['event_type' => 'PRODUCT_CREATED', 'topic' => 'inventory', 'reference_type' => 'product'],
            ['event_type' => 'BORROWING_RETURNED', 'topic' => 'borrowing', 'reference_type' => 'borrowing'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $type = $eventTypes[array_rand($eventTypes)];
            $refId = $type['reference_type'] === 'product' ? rand(1, 15) : rand(1, 4);

            EventLog::create([
                'event_type' => $type['event_type'],
                'topic' => $type['topic'],
                'payload' => [
                    'action' => $type['event_type'],
                    'reference_id' => $refId,
                    'timestamp' => now()->subMinutes(rand(1, 10080))->toISOString(),
                    'user_id' => rand(1, 3),
                    'metadata' => ['source' => 'system', 'version' => '1.0'],
                ],
                'processed' => rand(0, 1) === 1,
                'result' => rand(0, 1) === 1 ? ['status' => 'success', 'output' => 'Processed successfully'] : null,
                'processing_time_ms' => rand(50, 2000),
                'reference_type' => $type['reference_type'],
                'reference_id' => $refId,
                'created_at' => now()->subMinutes(rand(1, 10080)),
            ]);
        }
    }
}

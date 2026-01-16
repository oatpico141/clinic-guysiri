<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientImportSeeder extends Seeder
{
    /**
     * Import patients from CSV file
     */
    public function run(): void
    {
        $csvFile = base_path('ประวัติคนไข้ คลินิกกายสิริ  สาขาลำลูกกา - ประวัติคนไข้กายสิริ.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        // Get or create branch for ลำลูกกา
        $branch = Branch::firstOrCreate(
            ['code' => 'LLK'],
            [
                'name' => 'สาขาลำลูกกา',
                'address' => 'ลำลูกกา ปทุมธานี',
                'phone' => '',
                'is_active' => true,
            ]
        );

        $this->command->info("Using branch: {$branch->name} (ID: {$branch->id})");

        // Read CSV
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error("Cannot open CSV file");
            return;
        }

        // Skip header row
        $header = fgetcsv($handle);

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty($row[2]) && empty($row[5])) {
                    continue;
                }

                try {
                    $hnNumber = trim($row[2] ?? '');
                    $gender = $this->parseGender($row[3] ?? '');
                    $nickname = trim($row[4] ?? '');
                    $firstName = trim($row[5] ?? '');
                    $lastName = trim($row[6] ?? '');
                    $phone = $this->cleanPhone($row[7] ?? '');
                    $idCard = $this->cleanIdCard($row[9] ?? '');
                    $chiefComplaint = trim($row[10] ?? '');
                    $birthDate = $this->parseThaiDate($row[11] ?? '');
                    $occupation = trim($row[13] ?? '');
                    $address = trim($row[14] ?? '');
                    $chronicDiseases = trim($row[17] ?? '');
                    $notes = trim($row[18] ?? '');

                    // Skip if no name
                    if (empty($firstName) && empty($lastName)) {
                        $skipped++;
                        continue;
                    }

                    // Build full name
                    $fullName = trim($firstName . ' ' . $lastName);

                    // Build notes with nickname and occupation
                    $notesArray = [];
                    if ($nickname) {
                        $notesArray[] = "ชื่อเล่น: {$nickname}";
                    }
                    if ($occupation && $occupation !== '-') {
                        $notesArray[] = "อาชีพ: {$occupation}";
                    }
                    if ($notes) {
                        $notesArray[] = $notes;
                    }
                    $combinedNotes = implode("\n", $notesArray);

                    // Check if patient already exists (by HN or ID card)
                    $exists = false;
                    if ($hnNumber) {
                        $exists = Patient::withoutGlobalScopes()->where('hn_number', $hnNumber)->exists();
                    }
                    if (!$exists && $idCard) {
                        $exists = Patient::withoutGlobalScopes()->where('id_card', $idCard)->exists();
                    }

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Create patient - use empty string for phone if null
                    $patientData = [
                        'hn_number' => $hnNumber ?: null,
                        'name' => $fullName,
                        'first_name' => $firstName ?: null,
                        'last_name' => $lastName ?: null,
                        'phone' => $phone ?: '',
                        'gender' => $gender,
                        'id_card' => $idCard ?: null,
                        'date_of_birth' => $birthDate,
                        'birth_date' => $birthDate,
                        'chief_complaint' => $chiefComplaint ?: null,
                        'address' => $address ?: null,
                        'chronic_diseases' => $chronicDiseases ?: null,
                        'notes' => $combinedNotes ?: null,
                        'branch_id' => $branch->id,
                        'first_visit_branch_id' => $branch->id,
                        'is_temporary' => false,
                    ];

                    Patient::withoutGlobalScopes()->create($patientData);

                    $imported++;

                    if ($imported % 100 === 0) {
                        $this->command->info("Imported {$imported} patients...");
                    }

                } catch (\Exception $e) {
                    $errors++;
                    $this->command->warn("Error on row: " . ($row[2] ?? 'unknown') . " - " . $e->getMessage());
                }
            }

            DB::commit();

            $this->command->info("=================================");
            $this->command->info("Import completed!");
            $this->command->info("Imported: {$imported}");
            $this->command->info("Skipped (duplicates/empty): {$skipped}");
            $this->command->info("Errors: {$errors}");
            $this->command->info("=================================");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Import failed: " . $e->getMessage());
        }

        fclose($handle);
    }

    /**
     * Parse Thai gender to standard format
     */
    private function parseGender(?string $value): ?string
    {
        $value = trim($value ?? '');
        if ($value === 'ช' || strtolower($value) === 'm' || $value === 'ชาย') {
            return 'male';
        }
        if ($value === 'ญ' || strtolower($value) === 'f' || $value === 'หญิง') {
            return 'female';
        }
        return null;
    }

    /**
     * Clean phone number
     */
    private function cleanPhone(?string $value): ?string
    {
        if (empty($value)) return null;

        // Take first phone if multiple
        $value = explode(',', $value)[0];

        // Remove non-digits except +
        $cleaned = preg_replace('/[^0-9+]/', '', $value);

        return $cleaned ?: null;
    }

    /**
     * Clean ID card number
     */
    private function cleanIdCard(?string $value): ?string
    {
        if (empty($value)) return null;

        // Remove dashes and spaces
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Thai ID card should be 13 digits
        if (strlen($cleaned) === 13) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Parse Thai date format to Carbon
     */
    private function parseThaiDate(?string $value): ?Carbon
    {
        if (empty($value)) return null;

        $value = trim($value);

        // Handle "ปี 2488" format (year only)
        if (preg_match('/^ปี\s*(\d{4})$/', $value, $matches)) {
            $year = (int)$matches[1];
            if ($year > 2400) {
                $year -= 543; // Convert Buddhist year
            }
            return Carbon::create($year, 1, 1);
        }

        // Thai months mapping
        $thaiMonths = [
            'ม.ค.' => 1, 'มกราคม' => 1, 'ม.ค' => 1,
            'ก.พ.' => 2, 'กุมภาพันธ์' => 2, 'ก.พ' => 2,
            'มี.ค.' => 3, 'มีนาคม' => 3, 'มี.ค' => 3,
            'เม.ย.' => 4, 'เมษายน' => 4, 'เม.ย' => 4,
            'พ.ค.' => 5, 'พฤษภาคม' => 5, 'พ.ค' => 5,
            'มิ.ย.' => 6, 'มิถุนายน' => 6, 'มิ.ย' => 6,
            'ก.ค.' => 7, 'กรกฎาคม' => 7, 'ก.ค' => 7,
            'ส.ค.' => 8, 'สิงหาคม' => 8, 'ส.ค' => 8,
            'ก.ย.' => 9, 'กันยายน' => 9, 'ก.ย' => 9,
            'ต.ค.' => 10, 'ตุลาคม' => 10, 'ต.ค' => 10,
            'พ.ย.' => 11, 'พฤศจิกายน' => 11, 'พ.ย' => 11,
            'ธ.ค.' => 12, 'ธันวาคม' => 12, 'ธ.ค' => 12,
        ];

        // Try to parse "26 ม.ค. 2552" or "9 พ.ย. 2537" format
        foreach ($thaiMonths as $thaiMonth => $monthNum) {
            if (stripos($value, $thaiMonth) !== false) {
                // Extract day and year
                if (preg_match('/(\d{1,2})\s*' . preg_quote($thaiMonth, '/') . '\s*(\d{4})/u', $value, $matches)) {
                    $day = (int)$matches[1];
                    $year = (int)$matches[2];

                    // Convert Buddhist year to Christian year
                    if ($year > 2400) {
                        $year -= 543;
                    }

                    try {
                        return Carbon::create($year, $monthNum, $day);
                    } catch (\Exception $e) {
                        return null;
                    }
                }
            }
        }

        return null;
    }
}

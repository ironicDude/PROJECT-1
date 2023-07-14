<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drug;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'labeller',
        'dosage_form',
        'strength',
        'strength',
        'route',
        'generic',
        'otc',
        'drug_id',
    ];

    public static function search(string $string, int $limit=5)
    {
        $products = DB::select("SELECT CONCAT(p.name,' ', '[', d.name, ']') AS product_name, MAX(p.id) AS product_id, MAX(d.id) AS drug_id
                                FROM drugs AS d
                                JOIN products AS p ON d.id = p.drug_id
                                WHERE p.name LIKE '%{$string}%' OR d.name LIKE '%{$string}%'
                                GROUP BY product_name
                                ORDER BY CHAR_LENGTH(product_name)
                                LIMIT {$limit}");
        return [$products];
    }
    /**
     * Relationships
     */
    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id', 'id');
    }

}

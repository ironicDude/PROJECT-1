<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Exceptions\AccountAlreadyRestoredException;
use App\Exceptions\AccountPermanentlyDeletedException;

use App\Mail\AccountDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable
{
    use SoftDeletes;
    use SingleTableInheritanceTrait;
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected static $singleTableTypeField = 'type';
    protected static $singleTableSubclasses = [Employee::class, Customer::class];
    // protected static $singleTableSubclasses = [Employee::class, Customer::class];

    protected static $persisted = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'remember_token',
        'mobile',
        'password',
        'address',
        'date_of_birth',
        'type',
        'gender',
        'image',
        'account_status',
        'deleted_at',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'type',
        'date_of_birth',
        'gender',
        'image',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->account_status = 'Blocked';

        // Save the updated user model to persist the changes in the database.
        $this->save();
    }

    /**
     *
     *
     * @return void
     */
    public function activate(): void
    {
        $this->account_status = 'Active';
        // Save the updated user model to persist the changes in the database.
        $this->save();
    }

    /**
     * Check if the user is directly allergic to a given product.
     *
     * This method checks if the user is directly allergic to the provided product.
     * It verifies whether the product exists in the collection of the user's allergies.
     *
     * @param \App\Models\Product $product The product to check for allergy.
     * @return bool Returns true if the user is directly allergic to the product, otherwise false.
     */
    public function isAllergicTo(Product $product)
    {
        return $this->allergies->contains($product);
    }
    /**
     * Check if the user is indirectly allergic to a given product.
     *
     * This method checks if the user is indirectly allergic to the provided product.
     * It retrieves all the products associated with the drugs of the user's allergies,
     * then excludes the products that are directly allergic to the user.
     * Finally, it checks if the provided product exists in the remaining collection.
     *
     * @param \App\Models\Product $product The product to check for indirect allergy.
     * @return bool Returns true if the user is indirectly allergic to the product, otherwise false.
     */
    public function isIndirectlyAllergicTo(Product $product)
    {
        // Get all products associated with the drugs of the user's allergies.
        $allergyDrugProducts = $this->allergies->pluck('drug.products')->flatten();

        // Exclude the products that are directly allergic to the user.
        $allergyDrugProducts = $allergyDrugProducts->diff($this->allergies);

        // Check if the provided product exists in the remaining collection.
        return $allergyDrugProducts->contains($product);
    }


    /**
     * Check if the authenticated user is a customer
     *
     * @return bool Returns true if the user is a customer and false if not.
     */
    public function isCustomer()
    {
        return $this->type == 'customer'
        ? true
        : false;
    }

    public function getAllergies()
    {
        $products = $this->allergies;
        return $products;
    }


    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }

    public function getImage()
    {
        $imageName = $this->image;
        // dd($imageName);
        if(!$imageName){
            return null;
        }
        $imageContent = file_get_contents("C:\\Programming\Laravel\PROJECT-1\storage\app\images\\{$imageName}");
        $encodedContent = base64_encode($imageContent);
        $imgExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $imageData = mb_convert_encoding("data:image/{$imgExtension};base64,{$encodedContent}", 'UTF-8');
        return $imageData;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getAccountStatus()
    {
        return $this->account_status;
    }

     /**
     * Get the address of the authenticated user.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
        $this->save();
        return $this->address;
    }

    public function setFirstName(string $firstName)
    {
        $this->first_name = $firstName;
        $this->save();
        return $this->first_name;
    }

    public function setLastName(string $lastName)
    {
        $this->last_name = $lastName;
        $this->save();
        return $this->last_name;
    }

    public function setMobile(int $mobile)
    {
        $this->mobile = $mobile;
        $this->save();
        return $this->mobile;
    }

    public function setGender(string $gender)
    {
        $this->gender = $gender;
        $this->save();
        return $this->gender;
    }

    public function setDateOfBirth(string $date)
    {
        $this->date_of_birth = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $this->save();
        return $this->date_of_birth;
    }

    public function setImage(UploadedFile $image)
    {
        if ($this->image) {
            Storage::disk('local')->delete("images/{$this->image}");
        }
        $imageName = "User{$this->id}.{$image->getClientOriginalExtension()}";
        Storage::disk('local')->put("images/{$imageName}", File::get($image));
        $this->image = $imageName;
        $this->save();
        return $this->getImage();
    }

    public function updateInfo(array $newInfo)
    {
        $this->setFirstName($newInfo['firstName']);
        $this->setLastName($newInfo['lastName']);
        $this->setEmail($newInfo['email']);
        $this->setMobile($newInfo['mobile']);
        $this->setAddress($newInfo['address']);
        $this->setDateOFBirth($newInfo['dateOfBirth']);
        $this->setGender($newInfo['gender']);
        return $this;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
        $this->save();
        return $this->email;
    }

    public function deleteSoftly()
    {
        Auth::logout();
        $this->delete();
        $url = URL::temporarySignedRoute('user.restore', now()->addDays(14), ['email' => $this->email]);
        Mail::to($this)->send(new AccountDeleted($this, $url));
    }

    public function restoreAccount()
    {
        if($this->deleted_at){
            if ($this->deleted_at->diffInDays(Carbon::now()) < 14) {
                $this->restore();
            }
            else{
                throw new AccountPermanentlyDeletedException('Your account has been deleted');
            }
        }
        else{
            throw new AccountAlreadyRestoredException('Your account is already activated');
        }
    }


    public function isEmployee()
    {
        return $this->type == 'employee';
    }

    public function getWishlistedProducts()
{
    $products = $this->wishlistedProducts;
    return $products;
}

    public function wishlistedProduct(Product $product)
    {
        return $this->wishlistedProducts->contains($product);
    }



    /**
     * relationships
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id', 'id');
    }

    public function wishlistedProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlisted_products', 'user_id', 'product_id');
    }

    public function allergies()
    {
        return $this->belongsToMany(Product::class, 'allergies', 'product_id', 'user_id');
    }
    public function employee_roles()
    {
        return $this->hasMany(User::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}

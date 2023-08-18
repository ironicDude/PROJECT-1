<?php

namespace App\Http\Controllers;

use App\Http\Resources\Application\VacancyCollection;
use App\Http\Resources\Application\VacancyResource;
use App\Http\Resources\CustomResponse;
use Illuminate\Http\Request;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VacancyController extends Controller
{
    use CustomResponse;
    public function index()
    {
        $this->authorize('viewAll', Vacancy::class);
        return new VacancyCollection(Vacancy::paginate(15));
    }

    public function show(Vacancy $vacancy)
    {
        $this->authorize('view', $vacancy);
        return self::customResponse('vacancy returned', new VacancyResource($vacancy), 200);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vacancy::class);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'salary' => 'required|numeric',
            'posting_date' => 'required|date',
            'deadline' => 'required|date',
            'number_of_vacancies' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $data = $request->all();
        $data['employee_id'] = Auth::user()->id;
        $vacancy = Vacancy::create($data);

        return self::customResponse('Vacancy created', new VacancyResource($vacancy), 200);
    }

    public function destroy(Vacancy $vacancy)
    {
        $this->authorize('delete', $vacancy);
        $vacancy->delete();
        return self::customResponse('Vacancy deleted', new VacancyResource($vacancy), 200);
    }

    public function update(Request $request, Vacancy $vacancy)
    {
        $this->authorize('update', $vacancy);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'salary' => 'required|numeric',
            'deadline' => 'required|date',
            'nubmer_of_vacancies' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $vacancy->update($request->all());

        return self::customResponse('Vacancy updated', new VacancyResource($vacancy), 200);

    }

}

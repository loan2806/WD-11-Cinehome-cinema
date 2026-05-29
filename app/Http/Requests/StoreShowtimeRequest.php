<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeRequest extends FormRequest
{
    /**
     * AUTHORIZE
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * RULES
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | MOVIE
            |--------------------------------------------------------------------------
            */
            'movie_id' => [
                'required',
                'exists:movies,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | CINEMA
            |--------------------------------------------------------------------------
            */
            'cinema_id' => [
                'required',
                'exists:cinemas,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | ROOM
            |--------------------------------------------------------------------------
            */
            'room_name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */
            'show_date' => [
                'required',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | TIME
            |--------------------------------------------------------------------------
            */
            'show_time' => [
                'required',
            ],

            /*
            |--------------------------------------------------------------------------
            | PRICE
            |--------------------------------------------------------------------------
            */
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    /**
     * MESSAGES
     */
    public function messages(): array
    {
        return [

            'movie_id.required' =>
                'Vui lòng chọn phim.',

            'movie_id.exists' =>
                'Phim không tồn tại.',

            'cinema_id.required' =>
                'Vui lòng chọn rạp.',

            'cinema_id.exists' =>
                'Rạp không tồn tại.',

            'room_name.required' =>
                'Vui lòng nhập tên phòng.',

            'show_date.required' =>
                'Vui lòng chọn ngày chiếu.',

            'show_date.date' =>
                'Ngày chiếu không hợp lệ.',

            'show_time.required' =>
                'Vui lòng chọn giờ chiếu.',

            'price.required' =>
                'Vui lòng nhập giá vé.',

            'price.numeric' =>
                'Giá vé phải là số.',

            'price.min' =>
                'Giá vé không hợp lệ.',
        ];
    }
}
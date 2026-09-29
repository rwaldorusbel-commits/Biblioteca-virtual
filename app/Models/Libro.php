<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    protected $table = 'libros';
    protected $primaryKey = 'ID_libro';
    public $timestamps = false;

    protected $fillable = ['Titulo', 'genero', 'Tipo', 'ID_autor', 'ID_editor', 'imagen'];

    public function autor()
    {
        return $this->belongsTo(Autor::class, 'ID_autor', 'ID_autores');
    }

    public function editor()
    {
        return $this->belongsTo(Editor::class, 'ID_editor', 'ID_editores');
    }
}

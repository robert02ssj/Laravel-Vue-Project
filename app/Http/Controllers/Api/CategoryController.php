<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

// Este controlador gestiona las categorías (Salas, Equipamiento, Instalaciones...).
// Solo admin/gestor pueden crear, editar y borrar. Cualquiera puede ver.

class CategoryController extends Controller
{
    // Listar todas las categorías
    public function index()
    {
        // withCount('resources') añade el campo "resources_count" para saber cuántos recursos tiene cada categoría
        return response()->json(Category::withCount('resources')->get());
    }

    // Ver una categoría concreta con todos sus recursos
    public function show(Category $category)
    {
        // load('resources') incluye en la respuesta los recursos que pertenecen a esta categoría
        return response()->json($category->load('resources'));
    }

    // Crear una categoría nueva (solo admin/gestor)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100', // nombre del icono (ej: mdi-laptop)
            'color'       => 'nullable|string|max:7',   // color en hexadecimal (ej: #3B82F6)
            'active'      => 'boolean',
        ]);

        // El slug se genera automáticamente en el modelo a partir del nombre
        $categoria = Category::create($datos);

        return response()->json($categoria, 201);
    }

    // Editar una categoría existente (solo admin/gestor)
    public function update(Request $request, Category $category)
    {
        $datos = $request->validate([
            'name'        => 'sometimes|string|max:255', // 'sometimes' = solo si viene en la petición
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:7',
            'active'      => 'boolean',
        ]);

        $category->update($datos);

        return response()->json($category);
    }

    // Borrar una categoría (solo admin/gestor)
    public function destroy(Category $category)
    {
        // SoftDelete: no se borra físicamente, se marca con deleted_at
        $category->delete();

        return response()->json(null, 204); // 204 = sin contenido, operación correcta
    }
}

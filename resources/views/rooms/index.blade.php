
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitaciones</title>
</head>
<body>
    <h1>Listado de habitaciones</h1>

    @if ($rooms->isEmpty())
        <p>No hay habitaciones registradas.</p>
    @else
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Precio por noche</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->type }}</td>
                        <td>${{ number_format($room->price_per_night, 2) }}</td>
                        <td>{{ $room->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
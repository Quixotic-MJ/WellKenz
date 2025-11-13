<table class="w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
            @foreach(array_keys((array)$data->first()) as $th)
                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ str_replace('_',' ',$th) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @foreach($data as $row)
            <tr class="hover:bg-gray-50 transition">
                @foreach((array)$row as $cell)
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $cell }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
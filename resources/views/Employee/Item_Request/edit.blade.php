@extends('Employee.layout.app')

@section('content')
    <div class="space-y-6">

        <!-- header -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Item Request</h1>
                    <p class="text-sm text-gray-500 mt-1">Modify your pending item request</p>
                </div>
                <a href="{{ route('Staff_Item_Request') }}" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Back to Requests</a>
            </div>
        </div>

        <!-- form -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('item-requests.update', $request->item_req_id) }}">@csrf @method('PUT')
                <div class="space-y-4 text-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                        <input type="text" name="item_req_name" value="{{ old('item_req_name', $request->item_req_name) }}" required
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <input type="text" name="item_req_unit" value="{{ old('item_req_unit', $request->item_req_unit) }}" required
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input type="number" name="item_req_quantity" value="{{ old('item_req_quantity', $request->item_req_quantity) }}" required min="1"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="item_req_description" rows="4" required
                                  class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">{{ old('item_req_description', $request->item_req_description) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('Staff_Item_Request') }}" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Update Request</button>
                </div>
            </form>
        </div>

    </div>
@endsection
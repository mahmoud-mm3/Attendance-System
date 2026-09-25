<x-layout title="فصل {{ $classRoom->name }}">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="page-title mb-1">{{ $classRoom->stage?->name }} - فصل {{ $classRoom->name }}</h1>
            <p class="page-subtitle">قائمة طلاب الفصل ({{ $students->count() }} طالبًا)</p>
        </div>
        <a href="/classes" class="btn-ghost">رجوع للفصول</a>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-right">
            <thead class="bg-ink-50 dark:bg-ink-800">
                <tr>
                    <th class="p-3">م</th>
                    <th class="p-3">اسم الطالب</th>
                    <th class="p-3">رقم الهوية</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100 dark:divide-ink-700">
                @forelse ($students as $student)
                    <tr>
                        <td class="p-3">{{ $student->serial_number }}</td>
                        <td class="p-3 font-medium">{{ $student->name }}</td>
                        <td class="p-3 text-ink-400">{{ $student->national_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-4 text-center text-ink-400">لا يوجد طلاب في هذا الفصل بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>

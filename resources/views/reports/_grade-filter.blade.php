{{--
    فلتر متسلسل: المرحلة (level) → الصف (Stage) → الفصل (ClassRoom).
    يُستخدم داخل <form> مفتوح بالفعل من الصفحة المستدعية.

    المتغيرات المطلوبة من الصفحة المستدعية:
    - $stages: كل الصفوف (Stage::orderBy('order')->get())
    - $classes: كل الفصول مع علاقة stage محمّلة (ClassRoom::ordered()->with('stage')->get())
    - $selectedLevel, $selectedStageId, $selectedClassId: القيم الحالية المختارة (قد تكون null)
--}}
@php
    $__levels = \App\Http\Controllers\StageController::LEVELS;
    $__stagesJson = $stages->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'level' => $s->level])->values();
    $__classesJson = $classes->map(fn ($c) => [
        'id' => $c->id,
        'stage_id' => $c->stage_id,
        'label' => ($c->stage?->name ?? '').' - فصل '.$c->name,
    ])->values();
@endphp

<div data-grade-filter
     data-stages="{{ $__stagesJson->toJson() }}"
     data-classes="{{ $__classesJson->toJson() }}"
     class="contents">

    <div class="flex-1 min-w-[160px]">
        <label class="label">المرحلة</label>
        <select name="level" data-level-select data-selected="{{ $selectedLevel }}" class="input">
            <option value="">كل المراحل</option>
            @foreach ($__levels as $lvl)
                <option value="{{ $lvl }}" {{ (string) $selectedLevel === (string) $lvl ? 'selected' : '' }}>المرحلة {{ $lvl }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 min-w-[160px]">
        <label class="label">الصف</label>
        <select name="stage_id" data-stage-select data-selected="{{ $selectedStageId }}" class="input">
            <option value="">كل الصفوف</option>
            @foreach ($stages as $stage)
                <option value="{{ $stage->id }}" {{ (string) $selectedStageId === (string) $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 min-w-[160px]">
        <label class="label">الفصل</label>
        <select name="class_id" data-class-select data-selected="{{ $selectedClassId }}" class="input">
            <option value="">كل الفصول</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" {{ (string) $selectedClassId === (string) $class->id ? 'selected' : '' }}>
                    {{ $class->stage?->name }} - فصل {{ $class->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@once
    <script>
        function __initGradeFilter(root) {
            const levelSel = root.querySelector('[data-level-select]');
            const stageSel = root.querySelector('[data-stage-select]');
            const classSel = root.querySelector('[data-class-select]');
            const stages = JSON.parse(root.dataset.stages || '[]');
            const classes = JSON.parse(root.dataset.classes || '[]');

            function rebuildStageOptions(keepSelected) {
                const level = levelSel.value;
                const selected = keepSelected ?? stageSel.value;
                const filtered = stages.filter(s => !level || s.level === level);
                stageSel.innerHTML = '<option value="">كل الصفوف</option>' +
                    filtered.map(s => `<option value="${s.id}" ${String(s.id) === String(selected) ? 'selected' : ''}>${s.name}</option>`).join('');
            }

            function rebuildClassOptions(keepSelected) {
                const stageId = stageSel.value;
                const level = levelSel.value;
                const selected = keepSelected ?? classSel.value;
                const stageIdsInLevel = level ? new Set(stages.filter(s => s.level === level).map(s => String(s.id))) : null;
                const filtered = classes.filter(c => {
                    if (stageId) return String(c.stage_id) === String(stageId);
                    if (stageIdsInLevel) return stageIdsInLevel.has(String(c.stage_id));
                    return true;
                });
                classSel.innerHTML = '<option value="">كل الفصول</option>' +
                    filtered.map(c => `<option value="${c.id}" ${String(c.id) === String(selected) ? 'selected' : ''}>${c.label}</option>`).join('');
            }

            levelSel.addEventListener('change', () => {
                rebuildStageOptions('');
                rebuildClassOptions('');
            });
            stageSel.addEventListener('change', () => {
                rebuildClassOptions('');
            });

            // إعادة بناء القوائم عند تحميل الصفحة، مع الحفاظ على القيم المختارة حاليًا من الرابط
            rebuildStageOptions(stageSel.dataset.selected);
            rebuildClassOptions(classSel.dataset.selected);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-grade-filter]').forEach(__initGradeFilter);
        });
    </script>
@endonce

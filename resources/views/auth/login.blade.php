<x-layout title="تسجيل الدخول">
    <div class="min-h-screen -m-4 sm:-m-6 flex flex-col lg:flex-row">
        {{-- الجانب الفني: اللوجو والهوية --}}
        <div class="brand-gradient lg:w-1/2 flex flex-col items-center justify-center text-center p-10 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, var(--color-gold-400) 0, transparent 35%), radial-gradient(circle at 80% 70%, var(--color-gold-400) 0, transparent 35%);"></div>

            <img src="{{ asset('images/logo.png') }}" alt="شعار مدارس أجيال أملج الأهلية"
                 class="w-40 h-40 sm:w-52 sm:h-52 rounded-full object-cover ring-4 ring-gold-400/40 shadow-2xl relative z-10 hover-lift">

            <h1 class="mt-6 text-2xl sm:text-3xl font-extrabold text-gold-300 relative z-10">مدارس أجيال أملج الأهلية</h1>
            <p class="mt-2 text-ink-300 relative z-10">نظام إدارة الحضور والغياب</p>
            <div class="mt-6 h-1 w-24 rounded-full gold-shimmer relative z-10"></div>
        </div>

        {{-- جانب الفورم --}}
        <div class="lg:w-1/2 flex items-center justify-center p-6 sm:p-10 bg-ink-50 dark:bg-ink-900">
            <div class="w-full max-w-sm">
                <h2 class="text-xl font-extrabold text-ink-900 dark:text-white mb-1">تسجيل الدخول</h2>
                <p class="text-sm text-ink-400 mb-6">أهلًا بك، سجِّل دخولك للمتابعة</p>

                @if ($errors->any())
                    <div class="rounded-xl px-4 py-2 mb-4 text-sm bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-800/50">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/login" class="flex flex-col gap-4">
                    @csrf
                    <div>
                        <label class="label">البريد الإلكتروني</label>
                        <input type="email" name="email" placeholder="name@ajyal-omluj.edu.sa" value="{{ old('email') }}" class="input" autofocus>
                    </div>
                    <div>
                        <label class="label">كلمة المرور</label>
                        <input type="password" name="password" placeholder="••••••••" class="input">
                    </div>
                    <button type="submit" class="btn-primary w-full mt-2 hover-lift">دخول</button>
                </form>
            </div>
        </div>
    </div>
</x-layout>

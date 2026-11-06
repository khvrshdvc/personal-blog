<x-layouts.admin title="Kirish" :shell="false">
    <div class="login">
        <form @class(['c', 'lg-c', 'shake' => $errors->any()]) method="POST" action="{{ route('admin.login.store') }}" novalidate>
            @csrf
            <span class="lg-ic" aria-hidden="true"><x-icon name="lock" /></span>
            <h1>Admin panel</h1>
            <p class="lg-s">Yozuvlar, loyihalar va sayt sozlamalarini boshqarish uchun kiring.</p>
            <label class="fld"><span>Email</span><span class="ipx"><input class="inp" type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" autocomplete="username" autofocus required></span></label>
            <label class="fld"><span>Parol</span><span class="ipx"><em><x-icon name="lock" /></em><input class="inp" type="password" name="password" placeholder="••••••" autocomplete="current-password" enterkeyhint="go" required></span></label>
            <p class="lg-err" aria-live="polite">{{ $errors->first('email') ?: $errors->first('password') }}</p>
            <button class="lg-btn" type="submit">Kirish <x-icon name="arrowUR" /></button>
            <div class="lg-f"><a href="{{ route('home') }}"><x-icon name="chevL" /> Saytga qaytish</a></div>
        </form>
    </div>
</x-layouts.admin>

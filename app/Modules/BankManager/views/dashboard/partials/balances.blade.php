<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

    <!-- Saldo Total Consolidado -->
    <a href="{{ route('bank-manager.account-balances.index') }}"
        class="p-6 rounded-xl shadow-xl bg-gradient-to-br from-blue-600 to-blue-700 text-white hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold">
                    {{ number_format($totalBalance, 2, ',', '.') }} €
                </h3>
                <p class="text-blue-100 mt-1">Saldo Total Consolidado</p>
            </div>
            <div class="text-5xl opacity-30">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </a>

    <!-- Saldo Pessoal -->
    <a href="{{ route('bank-manager.account-balances.index') }}"
        class="p-6 rounded-xl shadow-xl bg-gradient-to-br from-green-600 to-green-700 text-white hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold">
                    {{ number_format($personalBalance, 2, ',', '.') }} €
                </h3>
                <p class="text-green-100 mt-1">Saldo Pessoal</p>
            </div>
            <div class="text-5xl opacity-30">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </a>

    <!-- Saldo Empresarial -->
    <a href="{{ route('bank-manager.account-balances.index') }}"
        class="p-6 rounded-xl shadow-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white hover:shadow-2xl hover:scale-[1.02] transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold">
                    {{ number_format($businessBalance, 2, ',', '.') }} €
                </h3>
                <p class="text-amber-100 mt-1">Saldo Empresarial</p>
            </div>
            <div class="text-5xl opacity-30">
                <i class="fas fa-building"></i>
            </div>
        </div>
    </a>

</div>

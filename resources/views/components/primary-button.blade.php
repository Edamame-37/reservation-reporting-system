<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex justify-center items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transform active:scale-95 transition-all ease-in-out duration-200 shadow-md hover:shadow-lg']) }}>
    {{ $slot }}
</button>

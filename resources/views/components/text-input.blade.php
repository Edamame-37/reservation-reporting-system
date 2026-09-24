@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-3 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500/20 rounded-xl shadow-sm transition duration-200 ease-in-out']) }}>

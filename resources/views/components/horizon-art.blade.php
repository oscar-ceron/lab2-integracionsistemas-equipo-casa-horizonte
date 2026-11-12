{{-- Geometría del logo: sol dorado que sale sobre la línea del horizonte. --}}
<svg viewBox="0 0 400 500" preserveAspectRatio="xMidYMid slice" aria-hidden="true" {{ $attributes }}>
    <defs>
        <clipPath id="sky"><rect x="0" y="0" width="400" height="300" /></clipPath>
    </defs>
    <g clip-path="url(#sky)">
        <g class="sun">
            <circle cx="200" cy="300" r="190" fill="none" stroke="#d0ae6b" stroke-opacity=".12" />
            <circle cx="200" cy="300" r="150" fill="none" stroke="#d0ae6b" stroke-opacity=".2" />
            <circle cx="200" cy="300" r="110" fill="none" stroke="#d0ae6b" stroke-opacity=".35" />
            <circle cx="200" cy="300" r="70" fill="#d0ae6b" />
        </g>
    </g>
    <line x1="0" y1="300" x2="400" y2="300" stroke="#f6f5f1" stroke-opacity=".7" />
    <g stroke="#f6f5f1" stroke-opacity=".16">
        <line x1="60" y1="330" x2="340" y2="330" />
        <line x1="100" y1="356" x2="300" y2="356" />
        <line x1="140" y1="386" x2="260" y2="386" />
        <line x1="170" y1="420" x2="230" y2="420" />
    </g>
</svg>
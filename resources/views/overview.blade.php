<div class="dashboard-section">

    <h2>Overview</h2>
    <p>
        General overview of customer distribution and status.
    </p>


    <div class="graph-container">

        @foreach($images as $image)

            @if(
                str_contains($image, 'region') ||
                str_contains($image, 'status')
            )

                <div class="graph-card">

                    <img 
                        src="{{ $image }}" 
                        alt="Analytics Graph"
                    >

                </div>

            @endif

        @endforeach

    </div>

</div>
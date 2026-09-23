@extends('layouts.admin.app')
@section('title', 'Ad Reports')

@section('content')
<div class="content container-fluid">
    <h1>Ad Reports</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Impressions</h5>
                    <p class="card-text fs-2">{{ number_format($impressions) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Clicks</h5>
                    <p class="card-text fs-2">{{ number_format($clicks) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Overall CTR</h5>
                    <p class="card-text fs-2">{{ number_format($ctr, 2) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Performance (Last 30 Days)
        </div>
        <div class="card-body">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Top Performing Ads
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ad Name</th>
                        <th>Impressions</th>
                        <th>Clicks</th>
                        <th>CTR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topAds as $ad)
                        <tr>
                            <td>
                                <a href="{{ route('admin.ads.edit', $ad) }}">{{ $ad->name }}</a>
                                <br>
                                <span class="shortcode" title="Click to copy">{{ $ad->shortcode }}</span>
                            </td>
                            <td>{{ number_format($ad->impressions_count) }}</td>
                            <td>{{ number_format($ad->clicks_count) }}</td>
                            <td>{{ number_format($ad->ctr, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No ad performance data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chartData = @json($chartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Impressions',
                        data: chartData.impressions,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Clicks',
                        data: chartData.clicks,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Impressions' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Clicks' },
                        grid: { drawOnChartArea: false },
                    },
                }
            }
        });
    });
</script>
@endpush

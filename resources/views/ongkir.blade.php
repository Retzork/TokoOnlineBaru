<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cek Ongkir — Jajanan Tradisional</title>

    <style>
        :root {
            --primary: #f8694a;
            --primary-dark: #d95337;
            --ink: #30323a;
            --muted: #6b7280;
            --line: #e5e7eb;
            --bg: #f7f7f8;
            --danger: #b91c1c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 32px 16px;
            background: var(--bg);
            color: var(--ink);
            font-family: "Hind", "Segoe UI", Arial, sans-serif;
        }

        .wrap {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: .4px;
        }

        .topbar a {
            color: var(--primary-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .topbar a:hover {
            text-decoration: underline;
        }

        .card {
            padding: 24px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(48, 50, 58, .06);
        }

        .intro {
            margin: 0 0 20px;
            color: var(--muted);
            line-height: 1.55;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-warning {
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
        }

        .alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
        }

        .alert-info {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .location-section + .location-section {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px dashed var(--line);
        }

        .location-section h2 {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 12px;
            font-size: 15px;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .location-section h2::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-grid.locations {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        select,
        input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: var(--ink);
            font: inherit;
        }

        select:focus,
        input:focus {
            outline: 2px solid rgba(248, 105, 74, .25);
            border-color: var(--primary);
        }

        select:disabled,
        input:disabled {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .hint {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 22px;
        }

        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 20px;
            border: 0;
            border-radius: 6px;
            background: var(--primary);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s ease;
        }

        button:hover:not(:disabled) {
            background: var(--primary-dark);
        }

        button:disabled {
            cursor: not-allowed;
            opacity: .65;
        }

        #form-message {
            margin-top: 18px;
        }

        #form-message:empty {
            display: none;
        }

        .results {
            margin-top: 22px;
            border-top: 1px solid var(--line);
            padding-top: 20px;
        }

        .results[hidden] {
            display: none;
        }

        .results h2 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .results-summary {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 560px;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #fafafa;
            font-size: 12px;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        td.price {
            white-space: nowrap;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .empty-row {
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 700px) {
            .form-grid.locations {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 20px 12px;
            }

            .card {
                padding: 18px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="topbar">
            <h1>Cek Ongkir</h1>
            <a href="{{ route('beranda') }}">&larr; Kembali ke Toko</a>
        </div>

        <div class="card">
            <p class="intro">
                Halaman uji integrasi API RajaOngkir V2 (Minggu 7). Tentukan lokasi asal toko dan lokasi
                tujuan sampai tingkat kecamatan, isi berat paket dalam gram, lalu pilih kurir untuk
                melihat tarif dan estimasi pengiriman.
            </p>

            @if (blank(config('services.rajaongkir.api_key')))
            <div class="alert alert-warning">
                <strong>API key belum diatur.</strong>
                Isi <code>RAJAONGKIR_API_KEY</code> di file <code>.env</code>, lalu jalankan
                <code>php artisan config:clear</code>.
            </div>
            @endif

            <form id="ongkir-form" novalidate>
                <div class="location-section">
                    <h2>Lokasi Asal (Toko)</h2>
                    <div class="form-grid locations">
                        <div class="field">
                            <label for="origin-province">Provinsi Asal</label>
                            <select id="origin-province" disabled>
                                <option value="">Memuat provinsi...</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="origin-city">Kota / Kabupaten Asal</label>
                            <select id="origin-city" disabled>
                                <option value="">Pilih provinsi terlebih dahulu</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="origin-district">Kecamatan Asal</label>
                            <select id="origin-district" disabled>
                                <option value="">Pilih kota terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="location-section">
                    <h2>Lokasi Tujuan</h2>
                    <div class="form-grid locations">
                        <div class="field">
                            <label for="destination-province">Provinsi Tujuan</label>
                            <select id="destination-province" disabled>
                                <option value="">Memuat provinsi...</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="destination-city">Kota / Kabupaten Tujuan</label>
                            <select id="destination-city" disabled>
                                <option value="">Pilih provinsi terlebih dahulu</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="destination-district">Kecamatan Tujuan</label>
                            <select id="destination-district" disabled>
                                <option value="">Pilih kota terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="location-section">
                    <h2>Paket &amp; Kurir</h2>
                    <div class="form-grid">
                        <div class="field">
                            <label for="weight">Berat Paket (gram)</label>
                            <input id="weight" name="weight" type="number" min="1" step="1" value="1000" placeholder="Contoh: 1000">
                        </div>
                        <div class="field">
                            <label for="courier">Kurir</label>
                            <select id="courier" name="courier">
                                <option value="">Pilih kurir</option>
                                <option value="jne">JNE</option>
                                <option value="tiki">TIKI</option>
                                <option value="pos">POS Indonesia</option>
                            </select>
                        </div>
                        <div class="field full">
                            <span class="hint">
                                Data wilayah memakai endpoint RajaOngkir V2
                                (provinsi → kota/kabupaten → kecamatan).
                            </span>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button id="submit-btn" type="submit">Cek Ongkir</button>
                </div>
            </form>

            <div id="form-message" role="status" aria-live="polite"></div>

            <section id="results" class="results" hidden>
                <h2>Hasil Pengecekan</h2>
                <p id="results-summary" class="results-summary"></p>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kurir</th>
                                <th>Layanan</th>
                                <th>Keterangan</th>
                                <th>Biaya</th>
                                <th>Estimasi</th>
                            </tr>
                        </thead>
                        <tbody id="results-body"></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urls = {
                provinces: @json(route('ongkir.provinces')),
                cities: @json(route('ongkir.cities')),
                districts: @json(route('ongkir.districts')),
                cost: @json(route('ongkir.cost')),
            };
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const pickers = {
                origin: {
                    province: document.getElementById('origin-province'),
                    city: document.getElementById('origin-city'),
                    district: document.getElementById('origin-district'),
                },
                destination: {
                    province: document.getElementById('destination-province'),
                    city: document.getElementById('destination-city'),
                    district: document.getElementById('destination-district'),
                },
            };

            const courierSelect = document.getElementById('courier');
            const weightInput = document.getElementById('weight');
            const form = document.getElementById('ongkir-form');
            const submitButton = document.getElementById('submit-btn');
            const messageBox = document.getElementById('form-message');
            const resultsBox = document.getElementById('results');
            const resultsBody = document.getElementById('results-body');
            const resultsSummary = document.getElementById('results-summary');

            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            function showMessage(text, type) {
                messageBox.textContent = text || '';
                messageBox.className = text ? 'alert alert-' + (type || 'info') : '';
            }

            function setBusy(isBusy) {
                submitButton.disabled = isBusy;
                submitButton.textContent = isBusy ? 'Memproses...' : 'Cek Ongkir';
            }

            async function fetchJson(url, options) {
                const response = await fetch(url, {
                    ...(options || {}),
                    headers: {
                        Accept: 'application/json',
                        ...((options && options.headers) || {}),
                    },
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (error) {
                    throw new Error('Respons server bukan JSON yang valid.');
                }

                if (!data || !data.rajaongkir || !data.rajaongkir.status) {
                    throw new Error('Respons server tidak sesuai format RajaOngkir.');
                }

                if (Number(data.rajaongkir.status.code) !== 200) {
                    throw new Error(data.rajaongkir.status.description || 'Permintaan ke RajaOngkir gagal.');
                }

                return data.rajaongkir.results || [];
            }

            function resetSelect(select, text) {
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = text;
                select.appendChild(option);
                select.disabled = true;
            }

            function fillSelect(select, placeholder, items, valueKey, textKey) {
                select.innerHTML = '';

                const first = document.createElement('option');
                first.value = '';
                first.textContent = placeholder;
                select.appendChild(first);

                items.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item[valueKey];
                    option.textContent = item[textKey];
                    select.appendChild(option);
                });

                select.disabled = false;
            }

            async function loadProvinces() {
                try {
                    const provinces = await fetchJson(urls.provinces);

                    Object.values(pickers).forEach(function (picker) {
                        fillSelect(picker.province, 'Pilih provinsi', provinces, 'province_id', 'province');
                        resetSelect(picker.city, 'Pilih provinsi terlebih dahulu');
                        resetSelect(picker.district, 'Pilih kota terlebih dahulu');
                    });

                    showMessage('', '');
                } catch (error) {
                    Object.values(pickers).forEach(function (picker) {
                        resetSelect(picker.province, 'Provinsi tidak tersedia');
                    });
                    showMessage(error.message, 'error');
                }
            }

            function setupPicker(picker) {
                picker.province.addEventListener('change', async function () {
                    resetSelect(picker.city, 'Memuat kota...');
                    resetSelect(picker.district, 'Pilih kota terlebih dahulu');
                    resultsBox.hidden = true;

                    if (!this.value) {
                        resetSelect(picker.city, 'Pilih provinsi terlebih dahulu');
                        return;
                    }

                    try {
                        const cities = await fetchJson(
                            urls.cities + '?province_id=' + encodeURIComponent(this.value)
                        );

                        fillSelect(picker.city, 'Pilih kota / kabupaten', cities, 'city_id', 'city_name');
                        showMessage('', '');
                    } catch (error) {
                        resetSelect(picker.city, 'Kota tidak tersedia');
                        showMessage(error.message, 'error');
                    }
                });

                picker.city.addEventListener('change', async function () {
                    resetSelect(picker.district, 'Memuat kecamatan...');
                    resultsBox.hidden = true;

                    if (!this.value) {
                        resetSelect(picker.district, 'Pilih kota terlebih dahulu');
                        return;
                    }

                    try {
                        const districts = await fetchJson(
                            urls.districts + '?city_id=' + encodeURIComponent(this.value)
                        );

                        fillSelect(picker.district, 'Pilih kecamatan', districts, 'district_id', 'district_name');
                        showMessage('', '');
                    } catch (error) {
                        resetSelect(picker.district, 'Kecamatan tidak tersedia');
                        showMessage(error.message, 'error');
                    }
                });
            }

            function selectedText(select) {
                return select.options[select.selectedIndex]
                    ? select.options[select.selectedIndex].textContent
                    : '';
            }

            function formatEtd(etd) {
                if (!etd) {
                    return '-';
                }

                const text = String(etd).trim();

                if (/hari/i.test(text)) {
                    return text;
                }

                if (/day/i.test(text)) {
                    return text.replace(/\bdays?\b/gi, 'hari');
                }

                return text + ' hari';
            }

            function renderRates(rates) {
                resultsBody.innerHTML = '';
                resultsBox.hidden = false;

                if (!Array.isArray(rates) || rates.length === 0) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 5;
                    cell.className = 'empty-row';
                    cell.textContent = 'Tidak ada layanan pengiriman untuk pilihan ini.';
                    row.appendChild(cell);
                    resultsBody.appendChild(row);
                    return;
                }

                rates.forEach(function (rate) {
                    const row = document.createElement('tr');

                    const courier = document.createElement('td');
                    courier.textContent = rate.name || (rate.code ? rate.code.toUpperCase() : '-');

                    const service = document.createElement('td');
                    service.textContent = rate.service || '-';

                    const description = document.createElement('td');
                    description.textContent = rate.description || '-';

                    const price = document.createElement('td');
                    price.className = 'price';
                    price.textContent = rupiah.format(Number(rate.cost) || 0);

                    const estimate = document.createElement('td');
                    estimate.textContent = formatEtd(rate.etd);

                    row.appendChild(courier);
                    row.appendChild(service);
                    row.appendChild(description);
                    row.appendChild(price);
                    row.appendChild(estimate);
                    resultsBody.appendChild(row);
                });
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                resultsBox.hidden = true;

                const originDistrict = pickers.origin.district;
                const destinationDistrict = pickers.destination.district;
                const weight = Number.parseInt(weightInput.value, 10);
                const courier = courierSelect.value;

                if (!pickers.origin.province.value || !pickers.origin.city.value || !originDistrict.value) {
                    showMessage('Lengkapi lokasi asal sampai tingkat kecamatan terlebih dahulu.', 'error');
                    return;
                }

                if (!pickers.destination.province.value || !pickers.destination.city.value || !destinationDistrict.value) {
                    showMessage('Lengkapi lokasi tujuan sampai tingkat kecamatan terlebih dahulu.', 'error');
                    return;
                }

                if (!Number.isInteger(weight) || weight < 1) {
                    showMessage('Berat paket harus berupa angka minimal 1 gram.', 'error');
                    return;
                }

                if (!courier) {
                    showMessage('Pilih kurir pengiriman terlebih dahulu.', 'error');
                    return;
                }

                showMessage('Menghitung ongkos kirim...', 'info');
                setBusy(true);

                try {
                    const rates = await fetchJson(urls.cost, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            origin: originDistrict.value,
                            destination: destinationDistrict.value,
                            weight: weight,
                            courier: courier,
                        }),
                    });

                    const weightText = new Intl.NumberFormat('id-ID').format(weight);

                    resultsSummary.textContent =
                        selectedText(pickers.origin.district) +
                        ' → ' +
                        selectedText(destinationDistrict) +
                        ' — ' +
                        weightText +
                        ' gram via ' +
                        courier.toUpperCase();

                    renderRates(rates);
                    showMessage('Ongkos kirim berhasil dihitung.', 'info');
                } catch (error) {
                    showMessage(error.message, 'error');
                } finally {
                    setBusy(false);
                }
            });

            setupPicker(pickers.origin);
            setupPicker(pickers.destination);
            loadProvinces();
        });
    </script>
</body>

</html>

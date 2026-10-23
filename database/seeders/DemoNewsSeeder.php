<?php

namespace Database\Seeders;

use App\Models\Source;
use App\Services\News\Clusterer;
use App\Services\News\Ingestor;
use App\Services\News\MapService;
use Illuminate\Database\Seeder;

/**
 * Made-up stories for local development and screenshots. Nothing here is
 * real reporting; links point at example.com.
 */
class DemoNewsSeeder extends Seeder
{
    /**
     * [hours ago, source slug, title, summary]
     */
    private const ARTICLES = [
        // Addu flooding -> relief (one situation over several days)
        [190, 'sun', 'Heavy overnight rain floods homes across Addu City', 'Roads in Hithadhoo and Maradhoo were under water after heavy rain; the Met office issued a yellow alert for southern atolls.'],
        [189, 'edition', 'Addu City flooding: dozens of homes inundated after heavy rain', 'Flood water entered homes in Hithadhoo and Feydhoo overnight as heavy rain continued across Addu City.'],
        [188, 'atolltimes', 'Flooding in Addu City as heavy rain lashes south', 'Homes in Hithadhoo flooded after heavy rain; the council says pumps are being deployed.'],
        [187, 'psm', 'Heavy rain causes flooding in Addu City homes', 'NDMA teams are assessing flood damage in Hithadhoo and Maradhoo after heavy rain.'],
        [186, 'avas', 'އައްޑޫގައި ބޮޑު ވިއްސާރަ، ގެތަކަށް ފެން ވަދެއްޖެ', 'ހިތަދޫގައި ގިނަ ގެތަކަށް ފެން ވަދެއްޖެ'],
        [185, 'mihaaru', 'އައްޑޫގައި ބޮޑު ވިއްސާރަ: ގެތަކަށް ފެން ވަދެއްޖެ', 'ހިތަދޫ އާއި މަރަދޫގައި ފެން ބޮޑުވެއްޖެ'],
        [140, 'sun', 'NDMA sends flood relief supplies to Addu City', 'Pumps, sandbags and relief supplies were shipped to Hithadhoo as flood water recedes in Addu City.'],
        [139, 'edition', 'Flood relief: government ships supplies to Addu City', 'The NDMA shipment of pumps and relief supplies reached Hithadhoo, where homes were flooded last week.'],
        [138, 'atolltimes', 'Addu City flood relief supplies arrive in Hithadhoo', 'Relief supplies and pumps from the NDMA arrived for flooded homes in Addu City.'],
        [70, 'sun', 'Addu City council requests drainage upgrade after flooding', 'The council asked for a drainage project for Hithadhoo after last week\'s flooding of homes in Addu City.'],
        [69, 'psm', 'Addu City flooding: council seeks drainage upgrade for Hithadhoo', 'After homes flooded in Addu City, the council wants a new drainage network.'],

        // Hulhumalé housing
        [160, 'edition', 'Hulhumalé social housing flats handover begins', 'Keys to the first 1,000 social housing flats in Hulhumalé Phase II were handed over to applicants.'],
        [159, 'sun', 'First social housing flats handed over in Hulhumalé', 'Applicants received keys to social housing flats in Hulhumalé Phase II on Thursday.'],
        [158, 'corporatemaldives', 'HDC begins handover of Hulhumalé social housing flats', 'The Housing Development Corporation started handing over social housing flats in Hulhumalé Phase II.'],
        [157, 'psm', 'Social housing flats handover begins in Hulhumalé Phase II', 'The housing ministry says the handover of Hulhumalé flats will continue through the month.'],
        [40, 'atolltimes', 'Applicants protest delay in Hulhumalé social housing flats handover', 'Applicants gathered outside the housing ministry over delays in the handover of social housing flats in Hulhumalé.'],
        [39, 'edition', 'Social housing applicants protest Hulhumalé flats delay', 'Protesters said the handover of Hulhumalé social housing flats had stalled for applicants on the second list.'],

        // Tourism milestone
        [100, 'corporatemaldives', 'Maldives welcomes 1.8 millionth tourist arrival of the year', 'Tourist arrivals reached 1.8 million at Velana International Airport, ahead of last year, the tourism ministry said.'],
        [99, 'sun', 'Tourist arrivals cross 1.8 million milestone at Velana', 'The 1.8 millionth tourist of the year was welcomed at Velana International Airport with a ceremony.'],
        [98, 'edition', '1.8 million tourist arrivals: Velana ceremony marks milestone', 'China, Russia and India remain the top markets as tourist arrivals cross 1.8 million.'],
        [97, 'psm', 'Tourism ministry celebrates 1.8 million tourist arrivals', 'A welcome ceremony at Velana International Airport marked 1.8 million tourist arrivals.'],
        [96, 'maldivesindependent', 'Tourist arrivals hit 1.8 million as arrivals outpace last year', 'Tourist arrivals crossed 1.8 million, though resort occupancy in some atolls lags.'],
        [95, 'gnews', 'Maldives tourist arrivals surpass 1.8 million', 'Tourist arrivals to the Maldives reached 1.8 million at Velana International Airport.'],

        // Fuel price
        [30, 'sun', 'STO raises diesel and petrol prices from next week', 'State Trading Organisation said diesel and petrol prices will rise by 50 laari per litre.'],
        [29, 'corporatemaldives', 'Diesel and petrol prices to rise by 50 laari, says STO', 'STO cited global oil prices for the diesel and petrol price increase.'],
        [28, 'edition', 'Petrol and diesel prices go up: STO announces 50 laari increase', 'Fishermen say the diesel price increase will hit fishing costs in the atolls.'],

        // Thinadhoo harbour
        [20, 'atolltimes', 'Thinadhoo harbour expansion contract signed', 'The contract for expanding Thinadhoo harbour in Gaafu Dhaalu was signed with a local contractor.'],
        [19, 'psm', 'Contract signed for Thinadhoo harbour expansion project', 'The Thinadhoo harbour expansion in Gaafu Dhaalu is due to finish within 18 months.'],

        // Whale shark sighting, single source
        [12, 'atolltimes', 'Record whale shark sightings at Maamigili this season', 'Divers in Alif Dhaal report record whale shark sightings off Maamigili and Dhigurah.'],

        // Kulhudhuffushi hospital
        [8, 'sun', 'Kulhudhuffushi regional hospital opens new dialysis unit', 'The new dialysis unit in Kulhudhuffushi will serve patients across Haa Dhaalu and Haa Alif.'],
        [7, 'edition', 'New dialysis unit opens at Kulhudhuffushi regional hospital', 'Patients in Haa Alif and Haa Dhaalu will no longer need to travel to Malé for dialysis.'],
        [6, 'psm', 'Dialysis unit opens at Kulhudhuffushi hospital', 'Kulhudhuffushi regional hospital now offers dialysis for Haa Dhaalu patients.'],

        // Malé traffic
        [5, 'sun', 'New one-way traffic system starts in Malé', 'Police say the one-way traffic system on Majeedhee Magu in Malé starts on Sunday.'],
        [4, 'edition', 'Malé one-way traffic system begins on Majeedhee Magu', 'Drivers in Malé reported confusion on the first morning of the one-way traffic system.'],
        [4, 'maldivesindependent', 'Malé one-way traffic system draws mixed reactions', 'Motorists in Malé and Hulhumalé debated the new one-way traffic system.'],
        [3, 'psm', 'Police monitor Malé one-way traffic system on first day', 'Traffic police were stationed along Majeedhee Magu as Malé\'s one-way traffic system began.'],

        // Fuvahmulah tiger sharks
        [3, 'atolltimes', 'Fuvahmulah dive operators report surge in tiger shark dives', 'Tiger shark diving in Fuvahmulah draws record bookings this season.'],
        [2, 'corporatemaldives', 'Tiger shark diving bookings surge in Fuvahmulah', 'Fuvahmulah guesthouses report full occupancy as tiger shark dives draw visitors.'],

        // Laamu fishing
        [2, 'sun', 'Laamu fishermen land record yellowfin tuna catch', 'Fishermen from Fonadhoo and Isdhoo in Laamu sold record yellowfin tuna catch to the MIFCO plant.'],
        [1, 'edition', 'Record yellowfin tuna catch landed in Laamu', 'MIFCO says the yellowfin tuna catch in Laamu this week is the largest this year.'],
        [1, 'avas', 'ލާމުގައި ކަނޑުމަހުގެ ރެކޯޑް ބާނުމެއް', 'ފޮނަދޫގެ މަސްވެރިން'],
    ];

    public function run(Ingestor $ingestor, Clusterer $clusterer, MapService $map): void
    {
        Source::syncFromConfig();
        $sources = Source::all()->keyBy('slug');

        foreach (collect(self::ARTICLES)->groupBy(1) as $slug => $rows) {
            $items = $rows->map(fn ($row, $i) => [
                'guid' => hash('sha256', "demo:{$slug}:{$row[2]}"),
                'url' => 'https://example.com/'.$slug.'/'.\Illuminate\Support\Str::slug($row[2]) ?: 'https://example.com/'.$slug.'/'.$i,
                'title' => $row[2],
                'summary' => $row[3],
                'published_at' => now()->subHours($row[0])->subMinutes(($i * 17) % 50),
            ])->all();

            $ingestor->store($sources[$slug], $items);
        }

        $clusterer->run();

        for ($h = 23; $h >= 0; $h--) {
            $map->snapshot(now()->subHours($h));
        }
    }
}

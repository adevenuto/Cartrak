<?php

/*
 * Curated year/make/model catalogue for manual add-a-car.
 *
 * Deliberately a config array rather than database tables: this is static
 * reference data with no relationships and no user writes, and Phase 3's NHTSA
 * vPIC decode will supersede it as the primary path. Anything not listed is
 * handled by the form's free-text fallback, so the list never blocks a user.
 */

return [

    'min_year' => 1981, // 17-character VINs begin with the 1981 model year.

    'makes' => [
        'Acura' => ['ILX', 'Integra', 'MDX', 'RDX', 'TLX', 'TSX', 'ZDX'],
        'Audi' => ['A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'e-tron'],
        'BMW' => ['2 Series', '3 Series', '4 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X7', 'i4', 'iX'],
        'Buick' => ['Enclave', 'Encore', 'Envision', 'LaCrosse', 'Regal'],
        'Cadillac' => ['ATS', 'CT4', 'CT5', 'Escalade', 'CTS', 'XT4', 'XT5', 'XT6', 'Lyriq'],
        'Chevrolet' => ['Blazer', 'Bolt', 'Camaro', 'Colorado', 'Corvette', 'Equinox', 'Impala', 'Malibu', 'Silverado 1500', 'Suburban', 'Tahoe', 'Traverse', 'Trax'],
        'Chrysler' => ['300', 'Pacifica', 'Town & Country', 'Voyager'],
        'Dodge' => ['Challenger', 'Charger', 'Durango', 'Grand Caravan', 'Journey'],
        'Ford' => ['Bronco', 'Bronco Sport', 'Edge', 'Escape', 'Expedition', 'Explorer', 'F-150', 'F-250', 'Fusion', 'Maverick', 'Mustang', 'Mustang Mach-E', 'Ranger', 'Transit'],
        'Genesis' => ['G70', 'G80', 'G90', 'GV70', 'GV80'],
        'GMC' => ['Acadia', 'Canyon', 'Sierra 1500', 'Terrain', 'Yukon'],
        'Honda' => ['Accord', 'Civic', 'CR-V', 'Fit', 'HR-V', 'Insight', 'Odyssey', 'Passport', 'Pilot', 'Ridgeline'],
        'Hyundai' => ['Elantra', 'Ioniq 5', 'Kona', 'Palisade', 'Santa Fe', 'Sonata', 'Tucson', 'Venue'],
        'Infiniti' => ['Q50', 'Q60', 'QX50', 'QX60', 'QX80'],
        'Jaguar' => ['E-PACE', 'F-PACE', 'F-TYPE', 'I-PACE', 'XE', 'XF'],
        'Jeep' => ['Cherokee', 'Compass', 'Gladiator', 'Grand Cherokee', 'Renegade', 'Wagoneer', 'Wrangler'],
        'Kia' => ['Carnival', 'EV6', 'Forte', 'K5', 'Niro', 'Optima', 'Rio', 'Sedona', 'Seltos', 'Sorento', 'Soul', 'Sportage', 'Telluride'],
        'Land Rover' => ['Defender', 'Discovery', 'Range Rover', 'Range Rover Evoque', 'Range Rover Sport'],
        'Lexus' => ['ES', 'GX', 'IS', 'LS', 'NX', 'RX', 'UX'],
        'Lincoln' => ['Aviator', 'Corsair', 'Nautilus', 'Navigator'],
        'Mazda' => ['CX-30', 'CX-5', 'CX-50', 'CX-9', 'CX-90', 'Mazda3', 'Mazda6', 'MX-5 Miata'],
        'Mercedes-Benz' => ['A-Class', 'C-Class', 'E-Class', 'S-Class', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'Sprinter'],
        'MINI' => ['Clubman', 'Countryman', 'Hardtop'],
        'Mitsubishi' => ['Eclipse Cross', 'Outlander', 'Outlander Sport'],
        'Nissan' => ['Altima', 'Ariya', 'Frontier', 'Kicks', 'Leaf', 'Maxima', 'Murano', 'Pathfinder', 'Rogue', 'Sentra', 'Titan', 'Versa'],
        'Porsche' => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan'],
        'Ram' => ['1500', '2500', '3500', 'ProMaster'],
        'Rivian' => ['R1S', 'R1T'],
        'Subaru' => ['Ascent', 'BRZ', 'Crosstrek', 'Forester', 'Impreza', 'Legacy', 'Outback', 'WRX'],
        'Tesla' => ['Model 3', 'Model S', 'Model X', 'Model Y', 'Cybertruck'],
        'Toyota' => ['4Runner', 'Avalon', 'Camry', 'Corolla', 'Corolla Cross', 'Highlander', 'Land Cruiser', 'Prius', 'RAV4', 'Sequoia', 'Sienna', 'Tacoma', 'Tundra', 'Venza'],
        'Volkswagen' => ['Atlas', 'Golf', 'ID.4', 'Jetta', 'Passat', 'Taos', 'Tiguan'],
        'Volvo' => ['S60', 'S90', 'V60', 'XC40', 'XC60', 'XC90'],
    ],

    /*
     * Vehicle photo handling. Uploads are accepted as JPEG/PNG and re-encoded to
     * WebP on the way in, so nothing user-supplied is ever served back verbatim.
     */
    'photos' => [
        'disk' => env('FILESYSTEM_DISK', 'local'),
        'max_per_vehicle' => 6,
        'max_kilobytes' => 10 * 1024,
        'max_dimension' => 6000,
        'long_edge' => 1600,
        'thumbnail_edge' => 400,
        'quality' => 82,
        'directory' => 'vehicle-photos',
    ],

    /*
     * Common automotive paint colours, offered as swatches when adding a car.
     * Ordered roughly by how common they are on US roads. Anything outside this
     * list is still reachable through the custom picker, so the palette is a
     * shortcut rather than a constraint.
     */
    'colors' => [
        ['name' => 'White', 'hex' => '#f4f4f5'],
        ['name' => 'Black', 'hex' => '#1b1b1d'],
        ['name' => 'Gray', 'hex' => '#6e7276'],
        ['name' => 'Silver', 'hex' => '#c0c4c8'],
        ['name' => 'Blue', 'hex' => '#1f4e9c'],
        ['name' => 'Red', 'hex' => '#b31d26'],
        ['name' => 'Burgundy', 'hex' => '#6a1520'],
        ['name' => 'Green', 'hex' => '#1e6f45'],
        ['name' => 'Beige', 'hex' => '#d6c7a8'],
        ['name' => 'Brown', 'hex' => '#6b4a2f'],
        ['name' => 'Gold', 'hex' => '#c9a227'],
        ['name' => 'Orange', 'hex' => '#e06a1b'],
    ],

    /*
     * Expense categories offered on the expense lane. Free text is still allowed,
     * so this is a convenience, not a constraint.
     */
    'expense_categories' => [
        'Insurance', 'Registration', 'Parking', 'Tolls', 'Car Wash',
        'Accessories', 'Repair', 'Towing', 'Storage', 'Other',
    ],

];

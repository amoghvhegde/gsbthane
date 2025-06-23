<?php
namespace App\Utils;

class CSVParser {
    /**
     * Parse a CSV file and return an array of associative arrays
     * 
     * @param string $filePath Path to the CSV file
     * @param string $delimiter The delimiter used in the CSV file (default: ',')
     * @param string $enclosure The enclosure character (default: '"')
     * @param string $escape The escape character (default: '\\')
     * @return array An array of associative arrays, where each array represents a row with column names as keys
     * @throws \Exception If file cannot be opened or parsed
     */
    public function parse($filePath, $delimiter = ',', $enclosure = '"', $escape = '\\') {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception("Could not open file: {$filePath}");
        }
        
        // Read column headers from first row
        $headers = fgetcsv($handle, 0, $delimiter, $enclosure, $escape);
        if ($headers === false || empty($headers)) {
            fclose($handle);
            throw new \Exception("Could not read headers from CSV file or file is empty");
        }
        
        // Normalize headers (trim whitespace and convert to uppercase for consistency)
        $headers = array_map(function($header) {
            return strtoupper(trim($header));
        }, $headers);
        
        $data = [];
        $rowNumber = 2; // Start from 2 since first row is headers
        
        while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                $rowNumber++;
                continue;
            }
            
            // Make sure row has same number of columns as headers
            if (count($row) !== count($headers)) {
                fclose($handle);
                throw new \Exception("Row {$rowNumber} has " . count($row) . " columns, but headers have " . count($headers) . " columns");
            }
            
            // Combine headers with row values
            $data[] = array_combine($headers, $row);
            $rowNumber++;
        }
        
        fclose($handle);
        return $data;
    }
    
    /**
     * Export data to a CSV file
     * 
     * @param array $data Array of associative arrays to export
     * @param string $filePath Path to the output CSV file
     * @param string $delimiter The delimiter to use in the CSV file (default: ',')
     * @param string $enclosure The enclosure character (default: '"')
     * @param string $escape The escape character (default: '\\')
     * @return bool True if export was successful
     * @throws \Exception If file cannot be created or written to
     */
    public function export($data, $filePath, $delimiter = ',', $enclosure = '"', $escape = '\\') {
        if (empty($data)) {
            throw new \Exception("No data to export");
        }
        
        $handle = fopen($filePath, 'w');
        if ($handle === false) {
            throw new \Exception("Could not open file for writing: {$filePath}");
        }
        
        // Write headers (column names) based on first row's keys
        $headers = array_keys($data[0]);
        if (fputcsv($handle, $headers, $delimiter, $enclosure, $escape) === false) {
            fclose($handle);
            throw new \Exception("Failed to write CSV headers");
        }
        
        // Write data rows
        foreach ($data as $row) {
            if (fputcsv($handle, $row, $delimiter, $enclosure, $escape) === false) {
                fclose($handle);
                throw new \Exception("Failed to write CSV data row");
            }
        }
        
        fclose($handle);
        return true;
    }
}
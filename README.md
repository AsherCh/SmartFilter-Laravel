# SmartFilter-Laravel
![license-MIT-blue](https://github.com/AsherCh/SmartFilter-Laravel/assets/91914268/5f0181a7-b408-442e-b781-7f87ede0e57a)


SmartFilter-Laravel is a feature-rich trait that elevates the filtering capabilities of your Laravel models. Effortlessly enhance your filtering workflow, reduce redundancy, and improve code efficiency with this powerful solution.

## Features

- Easy integration with existing models.
- Flexible filtering options for various data types.
- Supports filtering on nested relationships.
- Optimized query execution for efficient performance.
- Customizable filterable attributes to suit specific needs.

## Installation

To use the SmartFilter-Laravel trait, follow these steps:

1. Download the `SmartFilter` trait file.

2. Place the `SmartFilter` trait file in your Laravel project's directory, preferably in the `app/Traits` directory.

3. In your Laravel model, import the `SmartFilter` trait:

   ```php
   use App\Traits\SmartFilter;
   
   class YourModel extends Model
   {
       use SmartFilter;
   
       // Your model code...
   }
## Usage

With SmartFilter-Laravel, you can easily apply filters to your model queries, including nested and relationship filters. Here's an example of how to use it:

1. Define a protected array named filterableAttributes in your model, which specifies the filterable attributes and their corresponding operations. The keys in       the filterableAttributes array should match the provided filters, including the dot notation for nested or relationship filters (note: for nested/relation the     key will be relationName.attribute, and its value will be an array as shown below):
   
   ```php
        namespace App\Models;
        
        use App\Traits\SmartFilter;
        use Illuminate\Database\Eloquent\Model;
        
        class City extends Model
        {
            use SmartFilter;
        
            protected $filterableAttributes = [
                'name' => 'like',
                'city_code' => 'equals',
                'municipality' => 'like',
                'country.name' => [
                    'operation' => 'like',
                ],
                // Add more filterable attributes...
            ];
        }

    This ensures that only valid filters are applied to the query. You can customize the filterable attributes
    and operations based on your model's requirements.

2. Generate the query statement by calling the applyFilter method on the model instance, and pass the array of filters as an argument. For example:
   
         $results = $yourModel->applyFilter($filters)->get();
   
    In the above example, $yourModel represents an instance of your model, and $filters is an array containing the filters to apply for filtration. The
    applyFilter method applies the specified filters to the query, and get() retrieves the filtered results.

## Contributing
Contributions are welcome! If you have any ideas, suggestions, or bug reports, please open an issue or submit a pull request.

## License
This project is licensed under the MIT License. See the LICENSE file for details.

## Acknowledgments
SmartFilter-Laravel is built on top of the Laravel framework, which is a fantastic PHP framework for web artisans.

## Contact
If you have any questions or queries, please feel free to contact me at ashersharif4@gmail.com.

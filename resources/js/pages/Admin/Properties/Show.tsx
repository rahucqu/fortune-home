import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { 
    ArrowLeft, 
    Bath, 
    Bed, 
    Building, 
    Car, 
    DollarSign, 
    Edit, 
    Eye, 
    Heart, 
    Home, 
    MapPin, 
    MessageSquare, 
    Ruler, 
    Star, 
    User 
} from 'lucide-react';

interface PropertyShowProps {
    property: {
        id: number;
        title: string;
        slug: string;
        description: string;
        price: number;
        monthly_rent: number | null;
        currency: string;
        status: string;
        listing_type: string;
        bedrooms: number | null;
        bathrooms: number | null;
        total_rooms: number | null;
        area_sqft: number | null;
        land_area_sqft: number | null;
        floors: number | null;
        floor_number: number | null;
        built_year: number | null;
        address: string;
        latitude: number | null;
        longitude: number | null;
        postal_code: string | null;
        is_furnished: boolean;
        has_parking: boolean;
        parking_spaces: number;
        pet_friendly: boolean;
        is_featured: boolean;
        views_count: number;
        favorites_count: number;
        inquiries_count: number;
        created_at: string;
        updated_at: string;
        property_type: {
            id: number;
            name: string;
            icon: string;
        };
        location: {
            id: number;
            name: string;
            type: string;
            state: string;
            country: string;
        };
        agent: {
            id: number;
            name: string;
            email: string;
            phone: string | null;
            license_number: string | null;
        };
        amenities: Array<{
            id: number;
            name: string;
            category: string;
            icon: string;
        }>;
    };
}

export default function PropertyShow({ property }: PropertyShowProps) {
    const formatPrice = (price: number, currency: string) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency === 'BDT' ? 'USD' : currency,
        }).format(price);
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'available':
                return 'bg-green-100 text-green-800';
            case 'sold':
                return 'bg-red-100 text-red-800';
            case 'rented':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'draft':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const groupedAmenities = property.amenities.reduce((acc, amenity) => {
        if (!acc[amenity.category]) {
            acc[amenity.category] = [];
        }
        acc[amenity.category].push(amenity);
        return acc;
    }, {} as Record<string, typeof property.amenities>);

    return (
        <AdminLayout>
            <Head title={`Property: ${property.title}`} />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/admin/properties">
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Properties
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{property.title}</h1>
                            <p className="text-sm text-gray-500">Property ID: #{property.id}</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={`/admin/properties/${property.id}/edit`}>
                            <Button variant="outline">
                                <Edit className="h-4 w-4 mr-2" />
                                Edit Property
                            </Button>
                        </Link>
                        {property.is_featured && (
                            <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">
                                <Star className="h-3 w-3 mr-1" />
                                Featured
                            </Badge>
                        )}
                        <Badge className={getStatusColor(property.status)}>
                            {property.status.charAt(0).toUpperCase() + property.status.slice(1)}
                        </Badge>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Property Overview */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Property Overview</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div className="text-center p-4 bg-gray-50 rounded-lg">
                                        <DollarSign className="h-6 w-6 mx-auto mb-2 text-green-600" />
                                        <div className="text-lg font-semibold">
                                            {property.listing_type === 'rent' && property.monthly_rent
                                                ? formatPrice(property.monthly_rent, property.currency)
                                                : formatPrice(property.price, property.currency)}
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            {property.listing_type === 'rent' ? 'Monthly Rent' : 'Price'}
                                        </div>
                                    </div>
                                    {property.bedrooms && (
                                        <div className="text-center p-4 bg-gray-50 rounded-lg">
                                            <Bed className="h-6 w-6 mx-auto mb-2 text-blue-600" />
                                            <div className="text-lg font-semibold">{property.bedrooms}</div>
                                            <div className="text-sm text-gray-500">Bedrooms</div>
                                        </div>
                                    )}
                                    {property.bathrooms && (
                                        <div className="text-center p-4 bg-gray-50 rounded-lg">
                                            <Bath className="h-6 w-6 mx-auto mb-2 text-purple-600" />
                                            <div className="text-lg font-semibold">{property.bathrooms}</div>
                                            <div className="text-sm text-gray-500">Bathrooms</div>
                                        </div>
                                    )}
                                    {property.area_sqft && (
                                        <div className="text-center p-4 bg-gray-50 rounded-lg">
                                            <Ruler className="h-6 w-6 mx-auto mb-2 text-orange-600" />
                                            <div className="text-lg font-semibold">{property.area_sqft.toLocaleString()}</div>
                                            <div className="text-sm text-gray-500">Sq Ft</div>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Description */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Description</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-gray-700 leading-relaxed">{property.description}</p>
                            </CardContent>
                        </Card>

                        {/* Property Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Property Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-3">
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Property Type:</span>
                                            <span className="font-medium">{property.property_type.name}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Listing Type:</span>
                                            <span className="font-medium capitalize">{property.listing_type}</span>
                                        </div>
                                        {property.total_rooms && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Total Rooms:</span>
                                                <span className="font-medium">{property.total_rooms}</span>
                                            </div>
                                        )}
                                        {property.floors && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Floors:</span>
                                                <span className="font-medium">{property.floors}</span>
                                            </div>
                                        )}
                                        {property.floor_number && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Floor Number:</span>
                                                <span className="font-medium">{property.floor_number}</span>
                                            </div>
                                        )}
                                        {property.built_year && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Year Built:</span>
                                                <span className="font-medium">{property.built_year}</span>
                                            </div>
                                        )}
                                    </div>
                                    <div className="space-y-3">
                                        {property.land_area_sqft && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Land Area:</span>
                                                <span className="font-medium">{property.land_area_sqft.toLocaleString()} sq ft</span>
                                            </div>
                                        )}
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Furnished:</span>
                                            <span className="font-medium">{property.is_furnished ? 'Yes' : 'No'}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Parking:</span>
                                            <span className="font-medium">
                                                {property.has_parking ? `${property.parking_spaces} spaces` : 'No'}
                                            </span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-gray-600">Pet Friendly:</span>
                                            <span className="font-medium">{property.pet_friendly ? 'Yes' : 'No'}</span>
                                        </div>
                                        {property.postal_code && (
                                            <div className="flex justify-between">
                                                <span className="text-gray-600">Postal Code:</span>
                                                <span className="font-medium">{property.postal_code}</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Amenities */}
                        {Object.keys(groupedAmenities).length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Amenities</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-6">
                                        {Object.entries(groupedAmenities).map(([category, amenities]) => (
                                            <div key={category}>
                                                <h4 className="font-medium text-gray-900 mb-3 capitalize">
                                                    {category} Amenities
                                                </h4>
                                                <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                                                    {amenities.map((amenity) => (
                                                        <div
                                                            key={amenity.id}
                                                            className="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg"
                                                        >
                                                            <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                            <span className="text-sm">{amenity.name}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Location */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <MapPin className="h-5 w-5 mr-2" />
                                    Location
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <div className="font-medium">{property.location.name}</div>
                                    <div className="text-sm text-gray-500 capitalize">
                                        {property.location.type}, {property.location.state}
                                    </div>
                                </div>
                                <Separator />
                                <div>
                                    <div className="text-sm text-gray-600 mb-1">Address:</div>
                                    <div className="text-sm">{property.address}</div>
                                </div>
                                {(property.latitude && property.longitude) && (
                                    <div>
                                        <div className="text-sm text-gray-600 mb-1">Coordinates:</div>
                                        <div className="text-sm font-mono">
                                            {property.latitude}, {property.longitude}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Agent */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <User className="h-5 w-5 mr-2" />
                                    Listing Agent
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <div className="font-medium">{property.agent.name}</div>
                                    <div className="text-sm text-gray-500">{property.agent.email}</div>
                                </div>
                                {property.agent.phone && (
                                    <div>
                                        <div className="text-sm text-gray-600 mb-1">Phone:</div>
                                        <div className="text-sm">{property.agent.phone}</div>
                                    </div>
                                )}
                                {property.agent.license_number && (
                                    <div>
                                        <div className="text-sm text-gray-600 mb-1">License:</div>
                                        <div className="text-sm font-mono">{property.agent.license_number}</div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Statistics */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Statistics</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center">
                                        <Eye className="h-4 w-4 mr-2 text-gray-500" />
                                        <span className="text-sm">Views</span>
                                    </div>
                                    <span className="font-medium">{property.views_count.toLocaleString()}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center">
                                        <Heart className="h-4 w-4 mr-2 text-gray-500" />
                                        <span className="text-sm">Favorites</span>
                                    </div>
                                    <span className="font-medium">{property.favorites_count.toLocaleString()}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center">
                                        <MessageSquare className="h-4 w-4 mr-2 text-gray-500" />
                                        <span className="text-sm">Inquiries</span>
                                    </div>
                                    <span className="font-medium">{property.inquiries_count.toLocaleString()}</span>
                                </div>
                                <Separator />
                                <div className="text-xs text-gray-500">
                                    Created: {new Date(property.created_at).toLocaleDateString()}
                                </div>
                                <div className="text-xs text-gray-500">
                                    Updated: {new Date(property.updated_at).toLocaleDateString()}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
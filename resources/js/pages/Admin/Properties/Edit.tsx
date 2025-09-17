import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

interface PropertyEditProps {
    property: {
        id: number;
        title: string;
        description: string;
        price: number;
        monthly_rent: number | null;
        property_type_id: number;
        location_id: number;
        agent_id: number;
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
        parking_spaces: number;
        address: string;
        latitude: number | null;
        longitude: number | null;
        postal_code: string | null;
        is_furnished: boolean;
        has_parking: boolean;
        pet_friendly: boolean;
        is_featured: boolean;
        amenities: Array<{
            id: number;
            name: string;
            category: string;
        }>;
    };
    propertyTypes: Array<{
        id: number;
        name: string;
    }>;
    locations: Array<{
        id: number;
        name: string;
    }>;
    agents: Array<{
        id: number;
        name: string;
    }>;
    amenities: Array<{
        id: number;
        name: string;
        category: string;
    }>;
}

export default function PropertyEdit({ property, propertyTypes, locations, agents, amenities }: PropertyEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        title: property.title || '',
        description: property.description || '',
        price: property.price?.toString() || '',
        monthly_rent: property.monthly_rent?.toString() || '',
        property_type_id: property.property_type_id?.toString() || '',
        location_id: property.location_id?.toString() || '',
        agent_id: property.agent_id?.toString() || '',
        status: property.status || 'available',
        listing_type: property.listing_type || 'sale',
        bedrooms: property.bedrooms?.toString() || '',
        bathrooms: property.bathrooms?.toString() || '',
        total_rooms: property.total_rooms?.toString() || '',
        area_sqft: property.area_sqft?.toString() || '',
        land_area_sqft: property.land_area_sqft?.toString() || '',
        floors: property.floors?.toString() || '',
        floor_number: property.floor_number?.toString() || '',
        built_year: property.built_year?.toString() || '',
        parking_spaces: property.parking_spaces?.toString() || '',
        address: property.address || '',
        latitude: property.latitude?.toString() || '',
        longitude: property.longitude?.toString() || '',
        postal_code: property.postal_code || '',
        is_furnished: property.is_furnished || false,
        has_parking: property.has_parking || false,
        pet_friendly: property.pet_friendly || false,
        is_featured: property.is_featured || false,
        amenities: property.amenities?.map(a => a.id) || [] as number[],
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/admin/properties/${property.id}`);
    };

    const amenitiesByCategory = amenities.reduce((acc, amenity) => {
        if (!acc[amenity.category]) {
            acc[amenity.category] = [];
        }
        acc[amenity.category].push(amenity);
        return acc;
    }, {} as Record<string, typeof amenities>);

    const toggleAmenity = (amenityId: number) => {
        const newAmenities = data.amenities.includes(amenityId)
            ? data.amenities.filter(id => id !== amenityId)
            : [...data.amenities, amenityId];
        setData('amenities', newAmenities);
    };

    return (
        <AdminLayout>
            <Head title={`Edit Property: ${property.title}`} />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/properties">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Properties
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Edit Property</h1>
                        <p className="text-muted-foreground">
                            Update property information and details
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Update the basic details of the property
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="title">Property Title *</Label>
                                    <Input
                                        id="title"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="Beautiful 3BR Home in Downtown"
                                        required
                                    />
                                    {errors.title && <p className="text-sm text-red-600">{errors.title}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="listing_type">Listing Type *</Label>
                                    <Select
                                        value={data.listing_type}
                                        onValueChange={(value) => setData('listing_type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select listing type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sale">For Sale</SelectItem>
                                            <SelectItem value="rent">For Rent</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.listing_type && <p className="text-sm text-red-600">{errors.listing_type}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {data.listing_type === 'sale' ? (
                                    <div className="space-y-2">
                                        <Label htmlFor="price">Sale Price *</Label>
                                        <Input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            value={data.price}
                                            onChange={(e) => setData('price', e.target.value)}
                                            placeholder="500000"
                                            required
                                        />
                                        {errors.price && <p className="text-sm text-red-600">{errors.price}</p>}
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        <Label htmlFor="monthly_rent">Monthly Rent *</Label>
                                        <Input
                                            id="monthly_rent"
                                            type="number"
                                            step="0.01"
                                            value={data.monthly_rent}
                                            onChange={(e) => setData('monthly_rent', e.target.value)}
                                            placeholder="2500"
                                            required
                                        />
                                        {errors.monthly_rent && <p className="text-sm text-red-600">{errors.monthly_rent}</p>}
                                    </div>
                                )}

                                <div className="space-y-2">
                                    <Label htmlFor="status">Status *</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) => setData('status', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="available">Available</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="sold">Sold</SelectItem>
                                            <SelectItem value="rented">Rented</SelectItem>
                                            <SelectItem value="draft">Draft</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status && <p className="text-sm text-red-600">{errors.status}</p>}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description *</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Describe the property features, location, and highlights..."
                                    rows={4}
                                    required
                                />
                                {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Property Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Property Details</CardTitle>
                            <CardDescription>
                                Specify the property characteristics and features
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="property_type_id">Property Type *</Label>
                                    <Select
                                        value={data.property_type_id}
                                        onValueChange={(value) => setData('property_type_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select property type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {propertyTypes.map((type) => (
                                                <SelectItem key={type.id} value={type.id.toString()}>
                                                    {type.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.property_type_id && <p className="text-sm text-red-600">{errors.property_type_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="location_id">Location *</Label>
                                    <Select
                                        value={data.location_id}
                                        onValueChange={(value) => setData('location_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select location" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {locations.map((location) => (
                                                <SelectItem key={location.id} value={location.id.toString()}>
                                                    {location.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.location_id && <p className="text-sm text-red-600">{errors.location_id}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="agent_id">Agent *</Label>
                                    <Select
                                        value={data.agent_id}
                                        onValueChange={(value) => setData('agent_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select agent" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {agents.map((agent) => (
                                                <SelectItem key={agent.id} value={agent.id.toString()}>
                                                    {agent.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.agent_id && <p className="text-sm text-red-600">{errors.agent_id}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="bedrooms">Bedrooms</Label>
                                    <Input
                                        id="bedrooms"
                                        type="number"
                                        min="0"
                                        value={data.bedrooms}
                                        onChange={(e) => setData('bedrooms', e.target.value)}
                                        placeholder="3"
                                    />
                                    {errors.bedrooms && <p className="text-sm text-red-600">{errors.bedrooms}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="bathrooms">Bathrooms</Label>
                                    <Input
                                        id="bathrooms"
                                        type="number"
                                        min="0"
                                        step="0.5"
                                        value={data.bathrooms}
                                        onChange={(e) => setData('bathrooms', e.target.value)}
                                        placeholder="2"
                                    />
                                    {errors.bathrooms && <p className="text-sm text-red-600">{errors.bathrooms}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="total_rooms">Total Rooms</Label>
                                    <Input
                                        id="total_rooms"
                                        type="number"
                                        min="0"
                                        value={data.total_rooms}
                                        onChange={(e) => setData('total_rooms', e.target.value)}
                                        placeholder="8"
                                    />
                                    {errors.total_rooms && <p className="text-sm text-red-600">{errors.total_rooms}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="floors">Floors</Label>
                                    <Input
                                        id="floors"
                                        type="number"
                                        min="1"
                                        value={data.floors}
                                        onChange={(e) => setData('floors', e.target.value)}
                                        placeholder="2"
                                    />
                                    {errors.floors && <p className="text-sm text-red-600">{errors.floors}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="area_sqft">Area (sq ft)</Label>
                                    <Input
                                        id="area_sqft"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.area_sqft}
                                        onChange={(e) => setData('area_sqft', e.target.value)}
                                        placeholder="2000"
                                    />
                                    {errors.area_sqft && <p className="text-sm text-red-600">{errors.area_sqft}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="land_area_sqft">Land Area (sq ft)</Label>
                                    <Input
                                        id="land_area_sqft"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.land_area_sqft}
                                        onChange={(e) => setData('land_area_sqft', e.target.value)}
                                        placeholder="5000"
                                    />
                                    {errors.land_area_sqft && <p className="text-sm text-red-600">{errors.land_area_sqft}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="built_year">Year Built</Label>
                                    <Input
                                        id="built_year"
                                        type="number"
                                        min="1900"
                                        max={new Date().getFullYear()}
                                        value={data.built_year}
                                        onChange={(e) => setData('built_year', e.target.value)}
                                        placeholder="2020"
                                    />
                                    {errors.built_year && <p className="text-sm text-red-600">{errors.built_year}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Location Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Location Information</CardTitle>
                            <CardDescription>
                                Provide the address and location details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="address">Address *</Label>
                                <Input
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="123 Main Street, City, State 12345"
                                    required
                                />
                                {errors.address && <p className="text-sm text-red-600">{errors.address}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="postal_code">Postal Code</Label>
                                    <Input
                                        id="postal_code"
                                        value={data.postal_code}
                                        onChange={(e) => setData('postal_code', e.target.value)}
                                        placeholder="10001"
                                    />
                                    {errors.postal_code && <p className="text-sm text-red-600">{errors.postal_code}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        type="number"
                                        step="any"
                                        value={data.latitude}
                                        onChange={(e) => setData('latitude', e.target.value)}
                                        placeholder="40.7128"
                                    />
                                    {errors.latitude && <p className="text-sm text-red-600">{errors.latitude}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        type="number"
                                        step="any"
                                        value={data.longitude}
                                        onChange={(e) => setData('longitude', e.target.value)}
                                        placeholder="-74.0060"
                                    />
                                    {errors.longitude && <p className="text-sm text-red-600">{errors.longitude}</p>}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Property Features */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Property Features</CardTitle>
                            <CardDescription>
                                Set additional features and amenities
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="is_furnished">Furnished</Label>
                                        <Switch
                                            id="is_furnished"
                                            checked={data.is_furnished}
                                            onCheckedChange={(checked) => setData('is_furnished', checked)}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="has_parking">Has Parking</Label>
                                        <Switch
                                            id="has_parking"
                                            checked={data.has_parking}
                                            onCheckedChange={(checked) => setData('has_parking', checked)}
                                        />
                                    </div>

                                    {data.has_parking && (
                                        <div className="space-y-2">
                                            <Label htmlFor="parking_spaces">Parking Spaces</Label>
                                            <Input
                                                id="parking_spaces"
                                                type="number"
                                                min="0"
                                                value={data.parking_spaces}
                                                onChange={(e) => setData('parking_spaces', e.target.value)}
                                                placeholder="2"
                                            />
                                            {errors.parking_spaces && <p className="text-sm text-red-600">{errors.parking_spaces}</p>}
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="pet_friendly">Pet Friendly</Label>
                                        <Switch
                                            id="pet_friendly"
                                            checked={data.pet_friendly}
                                            onCheckedChange={(checked) => setData('pet_friendly', checked)}
                                        />
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="is_featured">Featured Property</Label>
                                        <Switch
                                            id="is_featured"
                                            checked={data.is_featured}
                                            onCheckedChange={(checked) => setData('is_featured', checked)}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Amenities */}
                            <div className="space-y-4">
                                <h4 className="font-medium">Amenities</h4>
                                <div className="space-y-6">
                                    {Object.entries(amenitiesByCategory).map(([category, categoryAmenities]) => (
                                        <div key={category}>
                                            <h5 className="font-medium text-sm text-gray-700 mb-3 capitalize">
                                                {category} Amenities
                                            </h5>
                                            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                                {categoryAmenities.map((amenity) => (
                                                    <div
                                                        key={amenity.id}
                                                        className="flex items-center space-x-2"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            id={`amenity-${amenity.id}`}
                                                            checked={data.amenities.includes(amenity.id)}
                                                            onChange={() => toggleAmenity(amenity.id)}
                                                            className="rounded border-gray-300"
                                                        />
                                                        <Label
                                                            htmlFor={`amenity-${amenity.id}`}
                                                            className="text-sm cursor-pointer"
                                                        >
                                                            {amenity.name}
                                                        </Label>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit Button */}
                    <div className="flex justify-end space-x-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/properties">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update Property'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
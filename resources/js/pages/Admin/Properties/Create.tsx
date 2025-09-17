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

interface PropertyCreateProps {
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

export default function PropertyCreate({ propertyTypes, locations, agents, amenities }: PropertyCreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        price: '',
        property_type_id: '',
        location_id: '',
        agent_id: '',
        status: 'available',
        listing_type: 'sale',
        bedrooms: '',
        bathrooms: '',
        area_sqft: '',
        lot_size_sqft: '',
        year_built: '',
        floors: '',
        parking_spaces: '',
        address: '',
        city: '',
        state: '',
        zip_code: '',
        country: 'United States',
        latitude: '',
        longitude: '',
        features: [] as string[],
        virtual_tour_url: '',
        video_url: '',
        mls_number: '',
        hoa_fee: '',
        property_tax: '',
        utilities_included: [] as string[],
        amenities: [] as number[],
        available_from: '',
        is_featured: false,
        is_active: true,
    });

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Properties', href: '/admin/properties' },
        { title: 'Create', href: '/admin/properties/create' },
    ];

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/admin/properties');
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
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Property" />

            <div className="space-y-6">
                <div className="flex items-center space-x-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/properties">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Properties
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Create Property</h1>
                        <p className="text-muted-foreground">
                            Add a new property to your listing inventory
                        </p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Basic Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Enter the basic details of the property
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
                                    <Label htmlFor="price">Price *</Label>
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
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description *</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Describe the property features, amenities, and selling points..."
                                    rows={4}
                                    required
                                />
                                {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="property_type_id">Property Type *</Label>
                                    <Select value={data.property_type_id} onValueChange={(value) => setData('property_type_id', value)}>
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
                                    <Label htmlFor="status">Status</Label>
                                    <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="available">Available</SelectItem>
                                            <SelectItem value="sold">Sold</SelectItem>
                                            <SelectItem value="rented">Rented</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="off_market">Off Market</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="listing_type">Listing Type</Label>
                                    <Select value={data.listing_type} onValueChange={(value) => setData('listing_type', value)}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="sale">For Sale</SelectItem>
                                            <SelectItem value="rent">For Rent</SelectItem>
                                            <SelectItem value="both">Both</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Property Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Property Details</CardTitle>
                            <CardDescription>
                                Specify the physical characteristics of the property
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
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
                                        placeholder="2.5"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="area_sqft">Area (sq ft)</Label>
                                    <Input
                                        id="area_sqft"
                                        type="number"
                                        min="0"
                                        value={data.area_sqft}
                                        onChange={(e) => setData('area_sqft', e.target.value)}
                                        placeholder="2000"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="lot_size_sqft">Lot Size (sq ft)</Label>
                                    <Input
                                        id="lot_size_sqft"
                                        type="number"
                                        min="0"
                                        value={data.lot_size_sqft}
                                        onChange={(e) => setData('lot_size_sqft', e.target.value)}
                                        placeholder="5000"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="year_built">Year Built</Label>
                                    <Input
                                        id="year_built"
                                        type="number"
                                        min="1800"
                                        max={new Date().getFullYear() + 5}
                                        value={data.year_built}
                                        onChange={(e) => setData('year_built', e.target.value)}
                                        placeholder="2020"
                                    />
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
                                </div>

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
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Location Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Location Information</CardTitle>
                            <CardDescription>
                                Enter the property address and location details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="location_id">Location *</Label>
                                    <Select value={data.location_id} onValueChange={(value) => setData('location_id', value)}>
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
                                    <Select value={data.agent_id} onValueChange={(value) => setData('agent_id', value)}>
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

                            <div className="space-y-2">
                                <Label htmlFor="address">Address *</Label>
                                <Input
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="123 Main Street"
                                    required
                                />
                                {errors.address && <p className="text-sm text-red-600">{errors.address}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="city">City *</Label>
                                    <Input
                                        id="city"
                                        value={data.city}
                                        onChange={(e) => setData('city', e.target.value)}
                                        placeholder="New York"
                                        required
                                    />
                                    {errors.city && <p className="text-sm text-red-600">{errors.city}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="state">State *</Label>
                                    <Input
                                        id="state"
                                        value={data.state}
                                        onChange={(e) => setData('state', e.target.value)}
                                        placeholder="NY"
                                        required
                                    />
                                    {errors.state && <p className="text-sm text-red-600">{errors.state}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="zip_code">ZIP Code *</Label>
                                    <Input
                                        id="zip_code"
                                        value={data.zip_code}
                                        onChange={(e) => setData('zip_code', e.target.value)}
                                        placeholder="10001"
                                        required
                                    />
                                    {errors.zip_code && <p className="text-sm text-red-600">{errors.zip_code}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="country">Country *</Label>
                                    <Input
                                        id="country"
                                        value={data.country}
                                        onChange={(e) => setData('country', e.target.value)}
                                        required
                                    />
                                    {errors.country && <p className="text-sm text-red-600">{errors.country}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Amenities */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Amenities</CardTitle>
                            <CardDescription>
                                Select the amenities available with this property
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {Object.entries(amenitiesByCategory).map(([category, categoryAmenities]) => (
                                    <div key={category} className="space-y-2">
                                        <h4 className="font-medium text-sm uppercase tracking-wide text-muted-foreground">
                                            {category}
                                        </h4>
                                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                            {categoryAmenities.map((amenity) => (
                                                <label key={amenity.id} className="flex items-center space-x-2 cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.amenities.includes(amenity.id)}
                                                        onChange={() => toggleAmenity(amenity.id)}
                                                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    />
                                                    <span className="text-sm">{amenity.name}</span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Additional Information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Additional Information</CardTitle>
                            <CardDescription>
                                Optional fields for additional property details
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="virtual_tour_url">Virtual Tour URL</Label>
                                    <Input
                                        id="virtual_tour_url"
                                        type="url"
                                        value={data.virtual_tour_url}
                                        onChange={(e) => setData('virtual_tour_url', e.target.value)}
                                        placeholder="https://example.com/virtual-tour"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="video_url">Video URL</Label>
                                    <Input
                                        id="video_url"
                                        type="url"
                                        value={data.video_url}
                                        onChange={(e) => setData('video_url', e.target.value)}
                                        placeholder="https://youtube.com/watch?v=..."
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="mls_number">MLS Number</Label>
                                    <Input
                                        id="mls_number"
                                        value={data.mls_number}
                                        onChange={(e) => setData('mls_number', e.target.value)}
                                        placeholder="MLS123456"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="hoa_fee">HOA Fee</Label>
                                    <Input
                                        id="hoa_fee"
                                        type="number"
                                        step="0.01"
                                        value={data.hoa_fee}
                                        onChange={(e) => setData('hoa_fee', e.target.value)}
                                        placeholder="150.00"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="property_tax">Property Tax (Annual)</Label>
                                    <Input
                                        id="property_tax"
                                        type="number"
                                        step="0.01"
                                        value={data.property_tax}
                                        onChange={(e) => setData('property_tax', e.target.value)}
                                        placeholder="5000.00"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="available_from">Available From</Label>
                                <Input
                                    id="available_from"
                                    type="date"
                                    value={data.available_from}
                                    onChange={(e) => setData('available_from', e.target.value)}
                                />
                            </div>

                            <div className="flex items-center space-x-6">
                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="is_featured"
                                        checked={data.is_featured}
                                        onCheckedChange={(checked) => setData('is_featured', checked)}
                                    />
                                    <Label htmlFor="is_featured">Featured Property</Label>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked)}
                                    />
                                    <Label htmlFor="is_active">Active</Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit */}
                    <div className="flex items-center justify-end space-x-4">
                        <Button variant="outline" asChild>
                            <Link href="/admin/properties">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Property'}
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

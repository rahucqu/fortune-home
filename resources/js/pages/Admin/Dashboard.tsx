import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/Admin/admin-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight, Building2, Calendar, DollarSign, Home, MapPin, Plus, Star, TrendingUp, User, UserPlus, Users, Users2 } from 'lucide-react';

interface DashboardProps {
    stats: {
        total_users: number;
        total_teams: number;
        users_this_month: number;
        teams_this_month: number;
    };
    propertyStats: {
        total_properties: number;
        available_properties: number;
        sold_properties: number;
        rented_properties: number;
        properties_this_month: number;
        featured_properties: number;
        total_value: number;
        average_price: number;
        total_agents: number;
        total_locations: number;
        total_amenities: number;
    };
    recent_users: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
        roles: Array<{ name: string }>;
    }>;
    recent_teams: Array<{
        id: number;
        name: string;
        created_at: string;
        owner: {
            name: string;
            email: string;
        };
    }>;
    recent_properties: Array<{
        id: number;
        title: string;
        price: number;
        status: string;
        listing_type: string;
        created_at: string;
        agent: {
            name: string;
        };
        location: {
            name: string;
        };
        property_type: {
            name: string;
        };
    }>;
}

export default function Dashboard({ stats, propertyStats, recent_users, recent_teams, recent_properties }: DashboardProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'available':
                return 'bg-green-100 text-green-800';
            case 'sold':
                return 'bg-blue-100 text-blue-800';
            case 'rented':
                return 'bg-purple-100 text-purple-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getListingTypeColor = (type: string) => {
        switch (type) {
            case 'sale':
                return 'bg-emerald-100 text-emerald-800';
            case 'rent':
                return 'bg-orange-100 text-orange-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const breadcrumbs = [
        { title: 'Admin', href: '/admin' },
        { title: 'Dashboard', href: '/admin' },
    ];

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex-1 space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Admin Dashboard</h2>
                        <p className="text-muted-foreground">Welcome back! Here's your overview.</p>
                    </div>
                    <div className="flex gap-4">
                        <Button asChild>
                            <Link href="/admin/properties/create">
                                <Plus className="h-4 w-4 mr-2" />
                                Add Property
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Property Management Stats */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Properties</CardTitle>
                            <Home className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.total_properties.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{propertyStats.properties_this_month} this month
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Available Properties</CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.available_properties.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                {Math.round((propertyStats.available_properties / propertyStats.total_properties) * 100)}% of total
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Portfolio Value</CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(propertyStats.total_value)}</div>
                            <p className="text-xs text-muted-foreground">
                                Avg: {formatCurrency(propertyStats.average_price)}
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Featured Properties</CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.featured_properties.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                {Math.round((propertyStats.featured_properties / propertyStats.total_properties) * 100)}% featured
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Property Status Distribution */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Properties Sold</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.sold_properties.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                {Math.round((propertyStats.sold_properties / propertyStats.total_properties) * 100)}% of total
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Properties Rented</CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.rented_properties.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                {Math.round((propertyStats.rented_properties / propertyStats.total_properties) * 100)}% of total
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Agents</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.total_agents.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                {propertyStats.total_locations} locations covered
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* User & Team Stats */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_users.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{stats.users_this_month} this month
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active Teams</CardTitle>
                            <Users2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_teams.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{stats.teams_this_month} this month
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Locations</CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.total_locations.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                Coverage areas
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Amenities</CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{propertyStats.total_amenities.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                Available features
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    {/* Recent Properties */}
                    <Card className="lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Recent Properties</CardTitle>
                                <CardDescription>
                                    Latest property listings added to the system
                                </CardDescription>
                            </div>
                            <Button variant="outline" size="sm" asChild>
                                <Link href="/admin/properties">
                                    View all
                                    <ArrowUpRight className="h-4 w-4 ml-1" />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_properties.map((property) => (
                                    <div key={property.id} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="space-y-1">
                                            <div className="flex items-center gap-2">
                                                <h4 className="font-semibold text-sm">{property.title}</h4>
                                                <Badge className={getStatusColor(property.status)}>
                                                    {property.status}
                                                </Badge>
                                                <Badge className={getListingTypeColor(property.listing_type)}>
                                                    {property.listing_type}
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                {property.agent.name} • {property.location.name} • {property.property_type.name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                Added {formatDate(property.created_at)}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold text-lg">{formatCurrency(property.price)}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Users */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Recent Users</CardTitle>
                                <CardDescription>
                                    Latest registered users
                                </CardDescription>
                            </div>
                            <Button variant="outline" size="sm" asChild>
                                <Link href="/admin/users">
                                    <UserPlus className="h-4 w-4" />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recent_users.map((user) => (
                                    <div key={user.id} className="flex items-center space-x-4">
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">{user.name}</p>
                                            <p className="text-xs text-muted-foreground">{user.email}</p>
                                            <div className="flex gap-1">
                                                {user.roles.map((role) => (
                                                    <Badge key={role.name} variant="secondary" className="text-xs">
                                                        {role.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            <Calendar className="h-3 w-3 inline mr-1" />
                                            {formatDate(user.created_at)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Teams */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Teams</CardTitle>
                            <CardDescription>
                                Latest team registrations
                            </CardDescription>
                        </div>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/admin/teams">
                                View all
                                <ArrowUpRight className="h-4 w-4 ml-1" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recent_teams.map((team) => (
                                <div key={team.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="space-y-1">
                                        <h4 className="font-semibold text-sm">{team.name}</h4>
                                        <p className="text-sm text-muted-foreground">
                                            Owner: {team.owner.name} ({team.owner.email})
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Created {formatDate(team.created_at)}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
